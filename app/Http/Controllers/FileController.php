<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Allowed MIME types for file upload
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    // Maximum file size: 10MB
    private const MAX_FILE_SIZE = 10240; // KB


    public function index()
    {
        $files = File::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('files.index', compact('files'));
    }

    public function create()
    {
        return view('files.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:' . self::MAX_FILE_SIZE,
                'mimes:jpeg,jpg,png,gif,webp,pdf,txt,doc,docx',
            ],
        ]);

        $uploadedFile = $request->file('file');
        $validationErrors = [];
        $mimeType = null;

        if ($validator->fails()) {
            $validationErrors = array_merge($validationErrors, $validator->errors()->all());
        }

        if ($uploadedFile) {
            $maxSizeInBytes = self::MAX_FILE_SIZE * 1024;
            $fileSize = $uploadedFile->getSize();
            
            if ($fileSize > $maxSizeInBytes) {
                $validationErrors[] = 'File size exceeds the maximum allowed size of ' . self::MAX_FILE_SIZE . ' KB.';
            }

            if ($fileSize === 0 || $fileSize === false) {
                $validationErrors[] = 'Invalid file or file is empty.';
            }

            $mimeType = $uploadedFile->getMimeType();
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $validationErrors[] = 'File type not allowed.';
            }
        }

        if (!empty($validationErrors)) {
            return back()->withErrors(['file' => $validationErrors[0]])->withInput();
        }

        $originalName = $uploadedFile->getClientOriginalName();
        $sanitizedOriginalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $extension = $uploadedFile->getClientOriginalExtension();
        $storedName = Str::random(40) . '.' . $extension;
        $path = $uploadedFile->storeAs('uploads/' . Auth::id(), $storedName, 'private');

        $file = File::create([
            'user_id' => Auth::id(),
            'original_name' => $sanitizedOriginalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $uploadedFile->getSize(),
            'path' => $path,
        ]);

        AuditLogService::logFile('upload', $file, 'success');

        return redirect()->route('files.index')
            ->with('success', 'File uploaded successfully!');
    }

    /**
     * Download file with access control
     */
    public function download(File $file)
    {
        // Access control: only owner can download
        if ($file->user_id !== Auth::id()) {
            // Log unauthorized download attempt
            AuditLogService::logUnauthorized('file_download', $file, "User attempted to download file owned by user {$file->user_id}");
            return redirect()
                ->route('files.index')
                ->with('error', 'Unauthorized access. You can only download your own files.');
        }

        // Verify file exists
        if (!Storage::disk('private')->exists($file->path)) {
            AuditLogService::logFile('download', $file, 'failed', 'File not found in storage');
            return redirect()
                ->route('files.index')
                ->with('error', 'File not found or is no longer available.');
        }

        // Log successful download
        AuditLogService::logFile('download', $file, 'success');

        // Return file download response
        return Storage::disk('private')->download(
            $file->path,
            $file->original_name
        );
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            AuditLogService::logUnauthorized('file_delete', $file, "User attempted to delete file owned by user {$file->user_id}");
            abort(403, 'Unauthorized access.');
        }

        AuditLogService::logFile('delete', $file, 'success');
        Storage::disk('private')->delete($file->path);
        $file->delete();

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully!');
    }
}
