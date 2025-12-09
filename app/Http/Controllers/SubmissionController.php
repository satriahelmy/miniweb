<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    // Note: Authentication middleware is applied in routes/web.php

    /**
     * Display all submissions for the authenticated user
     */
    public function index()
    {
        $submissions = Submission::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('submissions.index', compact('submissions'));
    }

    /**
     * Show the form for creating a new submission
     */
    public function create()
    {
        return view('submissions.create');
    }

    /**
     * Store a newly created submission
     */
    public function store(Request $request)
    {
        // Input validation and sanitization
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Sanitize input (Laravel automatically escapes in Blade, but we sanitize here too)
        $submission = Submission::create([
            'user_id' => Auth::id(),
            'title' => strip_tags($request->title ?? ''),
            'content' => strip_tags($request->content), // Remove HTML tags for security
        ]);

        // Log submission creation
        AuditLogService::logSubmission('create', $submission, 'success');

        return redirect()->route('submissions.index')
            ->with('success', 'Submission created successfully!');
    }

    /**
     * Display the specified submission
     */
    public function show(Submission $submission)
    {
        // Access control: only owner can view
        if ($submission->user_id !== Auth::id()) {
            // Log unauthorized view attempt
            AuditLogService::logUnauthorized('submission_view', $submission, "User attempted to view submission owned by user {$submission->user_id}");
            abort(403, 'Unauthorized access.');
        }

        return view('submissions.show', compact('submission'));
    }

    /**
     * Remove the specified submission
     */
    public function destroy(Submission $submission)
    {
        // Access control: only owner can delete
        if ($submission->user_id !== Auth::id()) {
            // Log unauthorized delete attempt
            AuditLogService::logUnauthorized('submission_delete', $submission, "User attempted to delete submission owned by user {$submission->user_id}");
            abort(403, 'Unauthorized access.');
        }

        // Log submission deletion before deleting
        AuditLogService::logSubmission('delete', $submission, 'success');

        $submission->delete();

        return redirect()->route('submissions.index')
            ->with('success', 'Submission deleted successfully!');
    }
}
