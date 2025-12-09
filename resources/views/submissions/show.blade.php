@extends('layouts.app')

@section('title', 'View Submission')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Submission Details</h1>
        <a href="{{ route('submissions.index') }}" class="text-gray-600 hover:text-gray-800">
            ← Back to List
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">
            {{ $submission->title ?: 'Untitled Submission' }}
        </h2>
        
        <div class="mb-6">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $submission->content }}</p>
        </div>
        
        <div class="border-t pt-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">
                Created: {{ $submission->created_at->format('M d, Y H:i') }}
            </p>
            <form method="POST" action="{{ route('submissions.destroy', $submission) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                    Delete Submission
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

