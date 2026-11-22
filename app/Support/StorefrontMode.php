<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Storefront ecommerce vs inquiry mode (site setting toggle).
 */
final class StorefrontMode
{
    public static function inquiryEnabled(?array $site = null): bool
    {
        $site ??= SiteSettingDefaults::mergeWithDatabase(SiteSetting::allKeyed());
        $value = strtolower(trim((string) ($site['storefront_inquiry_mode'] ?? '0')));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * User-facing labels for the active mode.
     *
     * @return array<string, string>
     */
    public static function labels(?array $site = null): array
    {
        if (self::inquiryEnabled($site)) {
            return [
                'add_to_cart' => __('Select item'),
                'bag' => __('Selected items'),
                'shopping_bag' => __('Selected items'),
                'view_bag' => __('Selected items'),
                'open_bag' => __('Open selected items'),
                'bag_empty' => __('No items selected.'),
                'added_to_cart' => __('Item selected.'),
                'added_to_cart_toast' => __(':product has been selected'),
                'updated_cart' => __('Selection updated.'),
                'error_add' => __('Could not select item.'),
                'error_other' => __('Could not update selection.'),
                'proceed_checkout' => __('Proceed to inquiry'),
                'checkout' => __('Inquiry'),
                'place_order' => __('Inquire'),
                'back_to_bag' => __('Back to selected items'),
                'address_section' => __('Address'),
                'order_received' => __('Inquiry recorded'),
            ];
        }

        return [
            'add_to_cart' => __('Add to bag'),
            'bag' => __('Bag'),
            'shopping_bag' => __('Shopping bag'),
            'view_bag' => __('View bag'),
            'open_bag' => __('Open shopping bag'),
            'bag_empty' => __('Your bag is empty.'),
            'added_to_cart' => __('Added to your bag.'),
            'added_to_cart_toast' => __(':product has been added to cart'),
            'updated_cart' => __('Bag updated.'),
            'error_add' => __('Could not add to bag.'),
            'error_other' => __('Could not update bag.'),
            'proceed_checkout' => __('Proceed to checkout'),
            'checkout' => __('Checkout'),
            'place_order' => __('Place order'),
            'back_to_bag' => __('Back to bag'),
            'address_section' => __('Shipping address'),
            'order_received' => __('Order received'),
        ];
    }

    /**
     * Digits-only international WhatsApp number for wa.me, or null if unset/invalid.
     */
    public static function inquiryWhatsAppDigits(?array $site = null): ?string
    {
        $site ??= SiteSettingDefaults::mergeWithDatabase(SiteSetting::allKeyed());
        $raw = trim((string) ($site['inquiry_whatsapp_number'] ?? ''));
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        // Need country code + local number (at least ~10 digits).
        if (strlen($digits) < 10) {
            return null;
        }

        return $digits;
    }
}
