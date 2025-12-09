@extends('layouts.app')

@section('title', 'Create Submission')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Submission</h1>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" action="{{ route('submissions.store') }}">
            @csrf
            
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                    Title (Optional)
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title') }}" 
                    maxlength="255"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('title') border-red-500 @enderror"
                >
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="10" 
                    required
                    maxlength="10000"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('content') border-red-500 @enderror"
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Maximum 10,000 characters</p>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('submissions.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Create Submission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

