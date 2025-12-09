@extends('layouts.app')

@section('title', 'My Submissions')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">My Submissions</h1>
    <a href="{{ route('submissions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
        Create New Submission
    </a>
</div>

@if($submissions->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="divide-y divide-gray-200">
            @foreach($submissions as $submission)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ $submission->title ?: 'Untitled Submission' }}
                            </h3>
                            <p class="text-gray-600 mb-2">{{ Str::limit($submission->content, 150) }}</p>
                            <p class="text-sm text-gray-500">
                                Created: {{ $submission->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('submissions.show', $submission) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                View
                            </a>
                            <form method="POST" action="{{ route('submissions.destroy', $submission) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        {{ $submissions->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <p class="text-gray-600 mb-4">You haven't created any submissions yet.</p>
        <a href="{{ route('submissions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
            Create Your First Submission
        </a>
    </div>
@endif
@endsection

