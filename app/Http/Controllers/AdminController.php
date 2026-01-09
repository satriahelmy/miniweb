<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'You cannot delete another admin account.');
        }

        AuditLogService::logUnauthorized('user_delete', $user, "Admin deleted user: {$user->email}");

        $userEmail = $user->email;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userEmail} has been deleted successfully.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'You cannot disable another admin account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'enabled' : 'disabled';
        AuditLogService::logUnauthorized('user_status_change', $user, "Admin {$action} user: {$user->email}");

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->email} has been {$action} successfully.");
    }
}
