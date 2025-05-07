<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Payment;
use App\Models\EventAttendee;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
  public function run(): void
  {
    $attendees = EventAttendee::all();

    $paymentMethods = ['bank_transfer', 'credit_card', 'e_wallet'];
    $statuses = ['pending', 'completed', 'failed', 'refunded'];

    foreach ($attendees as $attendee) {
      Payment::create([
        'attendee_id' => $attendee->id,
        'amount' => $attendee->event->price,
        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
        'status' => $statuses[array_rand($statuses)],
        'transaction_id' => 'TRX-' . strtoupper(substr(md5(rand()), 0, 12)),
        'payment_date' => now(),
        'payment_details' => json_encode([
          'bank_name' => 'Bank Central Asia',
          'account_number' => '1234567890',
          'account_name' => 'Event Spot',
        ]),
      ]);
    }
  }
}
