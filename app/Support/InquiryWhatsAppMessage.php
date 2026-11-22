<?php

namespace App\Support;

use App\Models\Order;

/**
 * Builds a pre-filled WhatsApp click-to-chat message from a saved inquiry order.
 */
final class InquiryWhatsAppMessage
{
    public static function fromOrder(Order $order): string
    {
        $order->loadMissing('items');

        $channel = match ((string) ($order->inquiry_channel ?? '')) {
            'walk_in' => 'Walk-in',
            'call' => 'Call',
            default => (string) ($order->inquiry_channel ?? '—'),
        };

        $slot = match ((string) ($order->inquiry_preferred_slot ?? '')) {
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'evening' => 'Evening',
            default => '',
        };

        $date = $order->inquiry_preferred_date
            ? $order->inquiry_preferred_date->format('Y-m-d')
            : '—';

        $address = trim(implode(', ', array_filter([
            $order->shipping_address_line1,
            $order->shipping_address_line2,
            $order->shipping_city,
            $order->shipping_state,
            $order->shipping_postal_code,
            $order->shipping_country,
        ])));

        $lines = [
            'Inquiry request',
            'Reference: '.$order->order_number,
            '',
            'Customer',
            'Name: '.$order->customer_name,
            'Email: '.$order->customer_email,
            'Phone: '.($order->customer_phone ?: '—'),
            'Address: '.($address !== '' ? $address : '—'),
            '',
            'Preferred contact: '.$channel,
            'Preferred date: '.$date,
        ];

        if ($slot !== '') {
            $lines[] = 'Preferred time: '.$slot;
        }

        $lines[] = '';
        $lines[] = 'Selected items';

        $maxItems = 12;
        $items = $order->items;
        $shown = 0;
        foreach ($items as $item) {
            if ($shown >= $maxItems) {
                $remaining = $items->count() - $maxItems;
                $lines[] = '+'.$remaining.' more item(s)';
                break;
            }
            $unit = Money::formatKobo((int) $item->unit_price);
            $lineTotal = Money::formatKobo((int) $item->line_total);
            $lines[] = sprintf(
                '- %s × %d @ %s = %s',
                $item->name,
                (int) $item->qty,
                $unit,
                $lineTotal
            );
            $shown++;
        }

        $lines[] = '';
        $lines[] = 'Estimated total: '.Money::formatKobo((int) $order->total);
        $lines[] = '';
        $lines[] = 'Sent from the website inquiry form.';

        $message = implode("\n", $lines);

        // WhatsApp URL practical limit — keep reference + total if we must trim.
        if (strlen(rawurlencode($message)) > 1800) {
            $short = [
                'Inquiry request',
                'Reference: '.$order->order_number,
                'Name: '.$order->customer_name,
                'Phone: '.($order->customer_phone ?: '—'),
                'Preferred contact: '.$channel,
                'Preferred date: '.$date,
                'Items: '.$items->count(),
                'Estimated total: '.Money::formatKobo((int) $order->total),
                'Sent from the website inquiry form.',
            ];
            $message = implode("\n", $short);
        }

        return $message;
    }

    public static function waMeUrl(string $digits, Order $order): string
    {
        $text = self::fromOrder($order);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }
}
