<?php

namespace Ja\PayPal\Actions\Orders;

/**
 * @resource https://developer.paypal.com/docs/api/orders/v2/#orders_create
 */

class CreateOrder
{
    public static function run(array $items): array
    {

        // Create order and return payment URL with order ID

        return [
            'paymentUrl' => '',
            'orderId' => '',
        ];
    }
}
