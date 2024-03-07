<?php

namespace Ja\PayPal\Models\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait HasPayPalCheckout
{
    public function createPayPalAccessToken(): string
    {
        $url = env('PAYPAL_API_URL');

        $tokenResponse = (
            Http::withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                ->withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'))
                ->withBody('grant_type=client_credentials')
                ->post("{$url}/v1/oauth2/token")
                ->throw()
        );

        return $tokenResponse['access_token'];
    }

    public function payPalOrderCompleted(): bool
    {
        return $this->paypal_order_id && ($this->payPalOrder()['status'] ?? null) === 'COMPLETED';
    }

    public function getPayPalOrderAttribute(): ?Collection
    {
        if (! $this->paypal_order_id) {
            return null;
        }

        $payPalOrder = Cache::get("order_{$this->id}_stripe_pi", fn () => (
            json_encode($this->payPalOrder())
        ));

        return collect(json_decode($payPalOrder, true));
    }

    public function payPalOrder(): ?array
    {
        $url = env('PAYPAL_API_URL');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->createPayPalAccessToken()}",
        ])->get("{$url}/v2/checkout/orders/{$this->paypal_order_id}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    public function createPayPalOrder(): self
    {
        if ($this->paypal_order_id) {
            return $this;
        }

        $url = env('PAYPAL_API_URL');
        $customer = $this->user ?? $this->customer;
        $address = $this->billingAddress ?? $this->shippingAddress;

        $response = (
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->createPayPalAccessToken()}",
            ])->post("{$url}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $this->number,
                    'amount' => [
                        'currency_code' => str($this->currency)->upper(),
                        'value' => $this->total / 100,
                    ],
                    'shipping' => [
                        'type' => 'SHIPPING',
                        'name' => ['full_name' => $customer->name],
                        'address' => [
                            'address_line_1' => $address->line1,
                            'address_line_2' => $address->line2,
                            'admin_area_2' => $address->city,
                            'admin_area_1' => $address->state,
                            'postal_code' => $address->postal_code,
                            'country_code' => $address->country,
                        ]
                    ]
                    // 'items' => []
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'brand_name' => config('app.name'),
                            'locale' => 'en-US',
                            'landing_page' => 'LOGIN',
                            'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                            'user_action' => 'PAY_NOW',
                            'return_url' => route('cart'),
                            'cancel_url' => route('cart')
                        ]
                    ]
                ],
                // 'payee' => [
                //     'email_address' => '',
                //     'merchant_id' => '',
                // ],
            ])
            ->throw()
        );

        $this->paypal_order_id = $response['id'];
        $this->save();

        return $this;
    }

    public function capturePaymentForPayPalOrder()
    {
        $url = env('PAYPAL_API_URL');

        Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->createPayPalAccessToken()}",
        ])
            ->withBody('{}') // Required otherwise "malformed json request" error
            ->post("{$url}/v2/checkout/orders/{$this->paypal_order_id}/capture")
            ->throw();

        return $this;
    }
}
