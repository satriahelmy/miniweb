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

    // Note: Authentication middleware is applied in routes/web.php

    /**
     * Display all files for the authenticated user
     */
    public function index()
    {
        $files = File::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('files.index', compact('files'));
    }

    /**
     * Show the form for uploading a file
     */
    public function create()
    {
        return view('files.create');
    }

    /**
     * Store uploaded file with sanitization
     */
    public function store(Request $request)
    {
        // Input validation
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

        // Collect all validation errors
        if ($validator->fails()) {
            $validationErrors = array_merge($validationErrors, $validator->errors()->all());
        }

        // DoS Protection: Explicit file size validation in bytes
        if ($uploadedFile) {
            $maxSizeInBytes = self::MAX_FILE_SIZE * 1024;
            $fileSize = $uploadedFile->getSize();
            
            if ($fileSize > $maxSizeInBytes) {
                $validationErrors[] = 'File size exceeds the maximum allowed size of ' . self::MAX_FILE_SIZE . ' KB.';
            }

            // Additional DoS protection: Check if file is actually uploaded (not empty)
            if ($fileSize === 0 || $fileSize === false) {
                $validationErrors[] = 'Invalid file or file is empty.';
            }

            // File sanitization: validate MIME type
            $mimeType = $uploadedFile->getMimeType();
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $validationErrors[] = 'File type not allowed.';
            }
        }

        // Return early if there are validation errors
        if (!empty($validationErrors)) {
            return back()->withErrors(['file' => $validationErrors[0]])->withInput();
        }

        // Sanitize filename: remove dangerous characters and create safe stored name
        $originalName = $uploadedFile->getClientOriginalName();
        $sanitizedOriginalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        
        // Generate unique stored name to prevent conflicts and path traversal
        $extension = $uploadedFile->getClientOriginalExtension();
        $storedName = Str::random(40) . '.' . $extension;

        // Store file in private storage (not publicly accessible)
        $path = $uploadedFile->storeAs('uploads/' . Auth::id(), $storedName, 'private');

        // Save file metadata to database
        $file = File::create([
            'user_id' => Auth::id(),
            'original_name' => $sanitizedOriginalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $uploadedFile->getSize(),
            'path' => $path,
        ]);

        // Log file upload
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
            abort(403, 'Unauthorized access. You can only download your own files.');
        }

        // Verify file exists
        if (!Storage::disk('private')->exists($file->path)) {
            AuditLogService::logFile('download', $file, 'failed', 'File not found in storage');
            abort(404, 'File not found.');
        }

        // Log successful download
        AuditLogService::logFile('download', $file, 'success');

        // Return file download response
        return Storage::disk('private')->download(
            $file->path,
            $file->original_name
        );
    }

    /**
     * Remove the specified file
     */
    public function destroy(File $file)
    {
        // Access control: only owner can delete
        if ($file->user_id !== Auth::id()) {
            // Log unauthorized delete attempt
            AuditLogService::logUnauthorized('file_delete', $file, "User attempted to delete file owned by user {$file->user_id}");
            abort(403, 'Unauthorized access.');
        }

        // Log file deletion before deleting
        AuditLogService::logFile('delete', $file, 'success');

        // Delete file from storage
        Storage::disk('private')->delete($file->path);

        // Delete record from database
        $file->delete();

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully!');
    }
}
