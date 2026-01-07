<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of all users
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Delete a user
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting other admins
        if ($user->role === 'admin') {
            return back()->with('error', 'You cannot delete another admin account.');
        }

        // Log the deletion
        AuditLogService::logUnauthorized('user_delete', $user, "Admin deleted user: {$user->email}");

        // Delete user (cascade will handle related records)
        $userEmail = $user->email;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userEmail} has been deleted successfully.");
    }

    /**
     * Toggle user active status (disable/enable)
     */
    public function toggleStatus(User $user)
    {
        // Prevent admin from disabling themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        // Prevent disabling other admins
        if ($user->role === 'admin') {
            return back()->with('error', 'You cannot disable another admin account.');
        }

        // Toggle status
        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'enabled' : 'disabled';
        
        // Log the action
        AuditLogService::logUnauthorized('user_status_change', $user, "Admin {$action} user: {$user->email}");

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->email} has been {$action} successfully.");
    }
}
