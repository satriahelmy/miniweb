<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{

    public function index()
    {
        $submissions = Submission::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('submissions.index', compact('submissions'));
    }

    public function create()
    {
        return view('submissions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $submission = Submission::create([
            'user_id' => Auth::id(),
            'title' => strip_tags($request->title ?? ''),
            'content' => strip_tags($request->content),
        ]);

        AuditLogService::logSubmission('create', $submission, 'success');

        return redirect()->route('submissions.index')
            ->with('success', 'Submission created successfully!');
    }

    public function show(Submission $submission)
    {
        if ($submission->user_id !== Auth::id()) {
            AuditLogService::logUnauthorized('submission_view', $submission, "User attempted to view submission owned by user {$submission->user_id}");
            abort(403, 'Unauthorized access.');
        }

        return view('submissions.show', compact('submission'));
    }

    public function destroy(Submission $submission)
    {
        if ($submission->user_id !== Auth::id()) {
            AuditLogService::logUnauthorized('submission_delete', $submission, "User attempted to delete submission owned by user {$submission->user_id}");
            abort(403, 'Unauthorized access.');
        }

        AuditLogService::logSubmission('delete', $submission, 'success');

        $submission->delete();

        return redirect()->route('submissions.index')
            ->with('success', 'Submission deleted successfully!');
    }
}
