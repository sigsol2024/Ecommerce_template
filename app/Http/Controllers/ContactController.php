<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Public contact form disabled to stop SMTP abuse via /contact submissions.
 */
class ContactController extends Controller
{
    public function disabled(Request $request)
    {
        abort(404);
    }
}
