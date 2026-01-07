@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
    <p class="text-gray-600">Welcome to your secure web application dashboard</p>
</div>

@if(Auth::user()->isAdmin())
<div class="mb-6 bg-purple-50 border border-purple-200 rounded-lg p-6">
    <h2 class="text-xl font-semibold text-purple-900 mb-2">Admin Panel</h2>
    <p class="text-purple-700 mb-4">Manage users, disable accounts, and view system information</p>
    <a href="{{ route('admin.users.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md inline-block">
        Manage Users
    </a>
</div>
@endif

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

@endsection

