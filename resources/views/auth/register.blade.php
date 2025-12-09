@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Register</h2>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                Name
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                required 
                autofocus
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
            >
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                Email
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
            >
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                Password
            </label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
            >
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-gray-600 text-xs mt-1">
                Password harus minimal 8 karakter dan mengandung:
            </p>
            <ul class="text-gray-600 text-xs mt-1 list-disc list-inside">
                <li>Huruf besar (A-Z)</li>
                <li>Huruf kecil (a-z)</li>
                <li>Angka (0-9)</li>
                <li>Karakter khusus (!@#$%^&*...)</li>
            </ul>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                Confirm Password
            </label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
        </div>

        <div class="flex items-center justify-between">
            <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
            >
                Register
            </button>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                Already have an account? Login here
            </a>
        </div>
    </form>
</div>
@endsection

