@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">User Management</h1>
    <p class="text-gray-600">Manage all users in the system</p>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->role === 'admin')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                Admin
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                User
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Disabled
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            @php
                                $isCurrentUser = $user->id === Auth::id();
                                $isAdmin = strtolower($user->role ?? '') === 'admin';
                                $canManage = !$isCurrentUser && !$isAdmin;
                            @endphp
                            
                            @if($canManage)
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                    @csrf
                                    @if($user->is_active)
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm text-white transition-colors duration-200"
                                            style="background-color:#f59e0b;" {{-- amber-500 --}}
                                            onclick="return confirm('Are you sure you want to disable this user?')"
                                        >
                                            Disable
                                        </button>
                                    @else
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm text-white transition-colors duration-200"
                                            style="background-color:#22c55e;" {{-- green-500 --}}
                                            onclick="return confirm('Are you sure you want to enable this user?')"
                                        >
                                            Enable
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm text-white transition-colors duration-200"
                                        style="background-color:#ef4444;" {{-- red-500 --}}
                                        onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                                    >
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="bg-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-medium">N/A</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
</div>
@endsection

