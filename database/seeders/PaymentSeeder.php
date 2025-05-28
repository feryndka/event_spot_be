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
    $paymentMethods = ['bank_transfer', 'credit_card', 'e_wallet'];
    $statuses = ['pending', 'completed', 'failed', 'refunded'];

    foreach ($attendees as $attendee) {
      $event = Event::find($attendee->event_id);
      $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
      $status = $statuses[array_rand($statuses)];

      $paymentDetails = [
        'event_id' => $event->id,
        'event_title' => $event->title,
        'user_id' => $attendee->user_id,
        'user_name' => User::find($attendee->user_id)->name,
        'user_email' => User::find($attendee->user_id)->email
      ];

      // Add payment method specific details
      switch ($paymentMethod) {
        case 'bank_transfer':
          $paymentDetails['bank_name'] = 'BCA';
          $paymentDetails['account_number'] = '1234567890';
          $paymentDetails['account_name'] = 'Event Spot';
          break;
        case 'credit_card':
          $paymentDetails['card_type'] = 'Visa';
          $paymentDetails['last_four'] = '4242';
          $paymentDetails['billing_address'] = [
            'street' => '123 Main St',
            'city' => 'Jakarta',
            'state' => 'DKI Jakarta',
            'zip' => '12345',
            'country' => 'Indonesia'
          ];
          break;
        case 'e_wallet':
          $paymentDetails['wallet_type'] = 'GoPay';
          $paymentDetails['wallet_number'] = '081234567890';
          break;
      }

      // Add status specific details
      if ($status === 'refunded') {
        $paymentDetails['refund_reason'] = 'Event cancelled';
        $paymentDetails['refund_date'] = now()->subDays(rand(1, 5))->toDateTimeString();
      }

      Payment::create([
        'attendee_id' => $attendee->id,
        'amount' => $event->price,
        'payment_method' => $paymentMethod,
        'transaction_id' => 'TRX-' . strtoupper(uniqid()),
        'status' => $status,
        'payment_date' => $status === 'completed' ? now()->subDays(rand(1, 10)) : null,
        'midtrans_snap_token' => $status === 'pending' ? 'MOCK-' . strtoupper(uniqid()) : null,
        'midtrans_order_id' => 'ORDER-' . strtoupper(uniqid()),
        'payment_details' => json_encode($paymentDetails)
      ]);

      // Update attendee status based on payment status
      if ($status === 'completed') {
        $attendee->status = 'registered';
        $attendee->ticket_code = 'TIX-' . strtoupper(uniqid());
      } elseif ($status === 'refunded') {
        $attendee->status = 'cancelled';
      }
      $attendee->save();
    }
  }
}
