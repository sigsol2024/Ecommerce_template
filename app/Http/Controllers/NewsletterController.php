<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Public newsletter signup disabled to stop SMTP abuse via /newsletter/subscribe.
 */
class NewsletterController extends Controller
{
    public function disabled(Request $request)
    {
        abort(404);
    }
}
