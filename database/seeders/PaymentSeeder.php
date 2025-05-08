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
    // Get all event attendees
    $attendees = EventAttendee::all();

    foreach ($attendees as $attendee) {
      $event = Event::find($attendee->event_id);

      Payment::create([
        'attendee_id' => $attendee->id,
        'amount' => $event->price,
        'payment_method' => 'credit_card',
        'transaction_id' => 'TRX-' . strtoupper(uniqid()),
        'status' => 'completed',
        'payment_date' => now(),
        'midtrans_snap_token' => null,
        'midtrans_order_id' => null,
        'payment_details' => json_encode([
          'card_type' => 'Visa',
          'last_four' => '4242',
          'billing_address' => [
            'street' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'USA'
          ]
        ])
      ]);
    }
  }
}
