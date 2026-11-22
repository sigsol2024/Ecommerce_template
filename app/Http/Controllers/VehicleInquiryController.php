<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Public product inquiry endpoint disabled to stop SMTP abuse.
 * Re-enable only with strong rate-limiting + captcha if needed later.
 */
class VehicleInquiryController extends Controller
{
    public function disabled(Request $request, string $slug = '')
    {
        abort(404);
    }
}
