<?php

namespace Ja\PayPal\Actions\Orders;

/**
 * @resource https://developer.paypal.com/docs/api/orders/v2/#orders_capture
 */

class CaptureOrderPayment
{
    public static function run(string $orderId): array
    {
        // Capture order payment using payment souce from order authorization step
        // (via URL that was returned from CreateOrder action)

        return [];
    }
}
