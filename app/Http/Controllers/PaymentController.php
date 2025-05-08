<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
  public function index()
  {
    $payments = Auth::user()->payments()
      ->with(['event:id,title,poster_image'])
      ->latest()
      ->paginate(10);

    return response()->json($payments);
  }

  public function store(Request $request, Event $event)
  {
    // Check if event is paid
    if ($event->is_free) {
      return response()->json([
        'message' => 'This is a free event'
      ], 400);
    }

    // Check if already paid
    if ($event->payments()->where('user_id', Auth::id())->exists()) {
      return response()->json([
        'message' => 'Payment already exists for this event'
      ], 400);
    }

    $request->validate([
      'payment_method' => 'required|string|in:credit_card,bank_transfer,e_wallet',
      'amount' => 'required|numeric|min:0'
    ]);

    $payment = $event->payments()->create([
      'user_id' => Auth::id(),
      'payment_method' => $request->payment_method,
      'amount' => $request->amount,
      'status' => 'pending',
      'payment_date' => now()
    ]);

    // Here you would typically integrate with a payment gateway
    // For now, we'll just mark it as paid
    $payment->update(['status' => 'paid']);

    return response()->json($payment, 201);
  }

  public function show(Payment $payment)
  {
    $this->authorize('view', $payment);

    return response()->json($payment->load('event'));
  }

  public function eventPaymentHistory(Event $event)
  {
    $payments = $event->payments()
      ->with(['user:id,name,email'])
      ->latest()
      ->paginate(10);

    return response()->json($payments);
  }
}
