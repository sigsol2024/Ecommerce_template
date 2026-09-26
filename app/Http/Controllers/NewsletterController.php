<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\SiteSettingDefaults;
use App\Services\Mail\OutboundMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class NewsletterController extends Controller
{
    /**
     * Public footer signup form.
     */
    public function subscribe(Request $request, OutboundMailService $mailer): RedirectResponse
    {
        if (SiteSetting::getValue('newsletter_enabled') !== '1') {
            abort(404);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            self::dispatchNewsletterLead($mailer, $validated['email']);

            return back()->with('status', __('Thank you for subscribing.'));
        } catch (RuntimeException) {
            return back()
                ->withInput()
                ->withErrors(['newsletter_email' => __('Mail is not configured or the request could not be sent.')]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['newsletter_email' => __('Could not complete subscription. Try again later.')]);
        }
    }

    /**
     * Optional signup from contact form; failures are swallowed so contact submission still succeeds.
     */
    public static function notifyIfEnabled(OutboundMailService $mailer, string $email): void
    {
        if (SiteSetting::getValue('newsletter_enabled') !== '1') {
            return;
        }

        try {
            self::dispatchNewsletterLead($mailer, $email);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @throws RuntimeException|Throwable
     */
    private static function dispatchNewsletterLead(OutboundMailService $mailer, string $email): void
    {
        $to = SiteSettingDefaults::resolvedNotifyEmail();

        $subject = __('Newsletter signup');
        $html = '<p>'.__('New newsletter opt-in').': <strong>'.e($email).'</strong></p>';
        $mailer->send($to, SiteSettingDefaults::resolvedNotifyRecipientName(), $subject, $html, $email, '');
    }
}
