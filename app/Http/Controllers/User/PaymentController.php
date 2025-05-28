<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Payment;
use App\Http\Resources\Api\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
  /**
   * Process payment for an event
   */
  public function process(Request $request, Event $event)
  {
    try {
      // Validate request
      $validator = Validator::make($request->all(), [
        'payment_method' => 'required|string|in:bank_transfer,credit_card,e_wallet'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Check if event is free
      if ($event->is_free) {
        return response()->json([
          'status' => 'error',
          'message' => 'This event is free and does not require payment'
        ], 400);
      }

      // Get or create attendee
      $user = Auth::user();
      $attendee = EventAttendee::firstOrCreate(
        [
          'event_id' => $event->id,
          'user_id' => $user->id
        ],
        [
          'status' => 'pending_payment'
        ]
      );

      // Check if payment already exists
      $existingPayment = Payment::where('attendee_id', $attendee->id)
        ->whereIn('status', ['pending', 'completed'])
        ->first();

      if ($existingPayment) {
        return response()->json([
          'status' => 'error',
          'message' => 'Payment already exists for this event'
        ], 400);
      }

      // Create payment record
      $payment = Payment::create([
        'attendee_id' => $attendee->id,
        'amount' => $event->price,
        'payment_method' => $request->payment_method,
        'status' => 'pending',
        'midtrans_order_id' => 'ORDER-' . Str::random(10),
        'payment_details' => [
          'event_id' => $event->id,
          'event_title' => $event->title,
          'user_id' => $user->id,
          'user_name' => $user->name,
          'user_email' => $user->email
        ]
      ]);

      // TODO: Integrate with Midtrans or other payment gateway
      // For now, we'll just return a mock response
      $payment->midtrans_snap_token = 'MOCK-' . Str::random(20);
      $payment->save();

      return response()->json([
        'status' => 'success',
        'message' => 'Payment initiated successfully',
        'data' => new PaymentResource($payment)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to process payment'
      ], 500);
    }
  }

  /**
   * Check payment status
   */
  public function checkStatus(Event $event)
  {
    try {
      $user = Auth::user();
      $attendee = EventAttendee::where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

      if (!$attendee) {
        return response()->json([
          'status' => 'error',
          'message' => 'No registration found for this event'
        ], 404);
      }

      $payment = Payment::where('attendee_id', $attendee->id)
        ->latest()
        ->first();

      if (!$payment) {
        return response()->json([
          'status' => 'error',
          'message' => 'No payment found for this registration'
        ], 404);
      }

      // TODO: Check actual payment status from Midtrans
      // For now, we'll just return the stored status

      return response()->json([
        'status' => 'success',
        'data' => new PaymentResource($payment)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to check payment status'
      ], 500);
    }
  }

  /**
   * Request refund for a payment
   */
  public function refund(Request $request, Event $event)
  {
    try {
      $validator = Validator::make($request->all(), [
        'reason' => 'required|string|min:10'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = Auth::user();
      $attendee = EventAttendee::where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

      if (!$attendee) {
        return response()->json([
          'status' => 'error',
          'message' => 'No registration found for this event'
        ], 404);
      }

      $payment = Payment::where('attendee_id', $attendee->id)
        ->where('status', 'completed')
        ->latest()
        ->first();

      if (!$payment) {
        return response()->json([
          'status' => 'error',
          'message' => 'No completed payment found for this registration'
        ], 404);
      }

      // Check if refund is already requested
      if ($payment->status === 'refunded') {
        return response()->json([
          'status' => 'error',
          'message' => 'Payment has already been refunded'
        ], 400);
      }

      // TODO: Process refund through Midtrans
      // For now, we'll just update the status
      $payment->status = 'refunded';
      $payment->payment_details = array_merge($payment->payment_details ?? [], [
        'refund_reason' => $request->reason,
        'refund_date' => now()->toDateTimeString()
      ]);
      $payment->save();

      // Update attendee status
      $attendee->status = 'cancelled';
      $attendee->save();

      return response()->json([
        'status' => 'success',
        'message' => 'Refund request processed successfully',
        'data' => new PaymentResource($payment)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to process refund request'
      ], 500);
    }
  }
}
