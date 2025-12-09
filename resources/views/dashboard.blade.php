@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
    <p class="text-gray-600">Welcome to your secure web application dashboard</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Submissions</h2>
        <p class="text-gray-600 mb-4">Manage your text submissions and comments</p>
        <div class="flex space-x-4">
            <a href="{{ route('submissions.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                View All
            </a>
            <a href="{{ route('submissions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                Create New
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Files</h2>
        <p class="text-gray-600 mb-4">Upload and manage your files securely</p>
        <div class="flex space-x-4">
            <a href="{{ route('files.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                View All
            </a>
            <a href="{{ route('files.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                Upload File
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Security Features</h2>
    <ul class="list-disc list-inside text-gray-600 space-y-2">
        <li>Password hashing using bcrypt</li>
        <li>CSRF protection enabled</li>
        <li>Input validation and sanitization</li>
        <li>Secure file upload with sanitization</li>
        <li>Access control for file downloads</li>
        <li>Secure session management</li>
    </ul>
</div>
@endsection

