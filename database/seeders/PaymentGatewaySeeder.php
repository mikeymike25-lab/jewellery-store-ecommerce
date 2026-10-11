<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payment_gateways')->delete();
        DB::table('payment_gateways')->insert([
            ['name' => 'Xendit', 'gateway_key' => 'xendit', 'display_name' => 'Credit Card / GCash / Maya via Xendit', 'description' => 'Secure online payments via Xendit Philippines', 'is_active' => true, 'created_at' => now()],
            ['name' => 'PayMongo', 'gateway_key' => 'paymongo', 'display_name' => 'Credit Card / GCash', 'description' => 'Secure payment via PayMongo', 'is_active' => true, 'created_at' => now()],
            ['name' => 'PayPal', 'gateway_key' => 'paypal', 'display_name' => 'PayPal Secure', 'description' => 'Pay with your PayPal account', 'is_active' => true, 'created_at' => now()],
            ['name' => 'Bank Transfer', 'gateway_key' => 'bank_transfer', 'display_name' => 'Direct Bank Transfer', 'description' => 'Manual bank deposit or transfer', 'is_active' => true, 'created_at' => now()],
        ]);
    }
}
