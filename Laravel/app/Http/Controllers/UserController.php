<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function deepLink(Request $request)
    {
        $deepLink = "callondoc://openPage/abc";

        // from the client side you can do deep link this way
        // <a href="myapp://product?id=123">Open Product in App</a>

        return redirect()->to($deepLink);
    }
}
