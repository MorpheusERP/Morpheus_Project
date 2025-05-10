<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Check if user is logged in
        if (!session('logged_in')) {
            return redirect()->route('auth.login');
        }
        
        // Get user type from session
        $userType = session('user_type');
        
        // Return the home view for all user types
        return view('home.home');
    }
    
    /**
     * Show the users management page (admin only).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showUsers()
    {
        // Check if user is logged in
        if (!session('logged_in')) {
            return redirect()->route('auth.login');
        }
        
        // Check if user is admin
        if (session('user_type') !== 'admin') {
            return redirect()->route('home');
        }
        
        return view('menu.usuarios');
    }
}