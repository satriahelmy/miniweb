@extends('layouts.app')

@section('title', 'My Files')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">My Files</h1>
    <a href="{{ route('files.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
        Upload File
    </a>
</div>

@if($files->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($files as $file)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $file->original_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $file->mime_type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ number_format($file->size / 1024, 2) }} KB</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $file->created_at->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('files.download', $file) }}" class="text-blue-600 hover:text-blue-900 mr-4">
                                    Download
                                </a>
                                <form method="POST" action="{{ route('files.destroy', $file) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $files->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <p class="text-gray-600 mb-4">You haven't uploaded any files yet.</p>
        <a href="{{ route('files.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
            Upload Your First File
        </a>
    </div>
@endif
@endsection

