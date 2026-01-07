@extends('layouts.app')

@section('title', 'Upload File')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Upload File</h1>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-6">
                <label for="file" class="block text-gray-700 text-sm font-bold mb-2">
                    Select File <span class="text-red-500">*</span>
                </label>
                <input 
                    type="file" 
                    id="file" 
                    name="file" 
                    required
                    accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('file') border-red-500 @enderror"
                >
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">
                    Allowed file types: JPEG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX<br>
                    Maximum file size: 10 MB
                </p>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('files.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Upload File
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

