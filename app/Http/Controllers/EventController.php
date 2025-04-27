<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{
  public function index(Request $request)
  {
    $query = Event::with(['category', 'promotor', 'tags'])
      ->published()
      ->approved();

    // Filter by category
    if ($request->has('category')) {
      $query->whereHas('category', function ($q) use ($request) {
        $q->where('slug', $request->category);
      });
    }

    // Filter by date
    if ($request->has('date')) {
      switch ($request->date) {
        case 'upcoming':
          $query->upcoming();
          break;
        case 'ongoing':
          $query->ongoing();
          break;
        case 'past':
          $query->past();
          break;
      }
    }

    // Filter by price
    if ($request->has('price')) {
      if ($request->price === 'free') {
        $query->free();
      } else {
        $query->paid();
      }
    }

    // Search
    if ($request->has('search')) {
      $query->where(function ($q) use ($request) {
        $q->where('title', 'like', '%' . $request->search . '%')
          ->orWhere('description', 'like', '%' . $request->search . '%');
      });
    }

    // Sort
    $sort = $request->get('sort', 'start_date');
    $direction = $request->get('direction', 'asc');
    $query->orderBy($sort, $direction);

    $events = $query->paginate(10);

    return response()->json($events);
  }

  public function show(Event $event)
  {
    $event->load(['category', 'promotor', 'tags', 'comments.user', 'statistics']);
    return response()->json($event);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'title' => 'required|string|max:255',
      'description' => 'required|string',
      'category_id' => 'required|exists:categories,id',
      'location_name' => 'required|string|max:255',
      'address' => 'required|string',
      'latitude' => 'nullable|numeric',
      'longitude' => 'nullable|numeric',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'registration_start' => 'required|date',
      'registration_end' => 'required|date|after:registration_start|before:start_date',
      'is_free' => 'required|boolean',
      'price' => 'required_if:is_free,false|numeric|min:0',
      'max_attendees' => 'nullable|integer|min:1',
      'poster_image' => 'nullable|image|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'exists:event_tags,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();
    $data['slug'] = Str::slug($request->title);
    $data['promotor_id'] = $request->user()->id;

    if ($request->hasFile('poster_image')) {
      $path = $request->file('poster_image')->store('event-posters', 'public');
      $data['poster_image'] = $path;
    }

    $event = Event::create($data);

    if ($request->has('tags')) {
      $event->tags()->attach($request->tags);
    }

    return response()->json($event, 201);
  }

  public function update(Request $request, Event $event)
  {
    if ($request->user()->id !== $event->promotor_id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
      'title' => 'sometimes|required|string|max:255',
      'description' => 'sometimes|required|string',
      'category_id' => 'sometimes|required|exists:categories,id',
      'location_name' => 'sometimes|required|string|max:255',
      'address' => 'sometimes|required|string',
      'latitude' => 'nullable|numeric',
      'longitude' => 'nullable|numeric',
      'start_date' => 'sometimes|required|date',
      'end_date' => 'sometimes|required|date|after:start_date',
      'registration_start' => 'sometimes|required|date',
      'registration_end' => 'sometimes|required|date|after:registration_start|before:start_date',
      'is_free' => 'sometimes|required|boolean',
      'price' => 'required_if:is_free,false|numeric|min:0',
      'max_attendees' => 'nullable|integer|min:1',
      'poster_image' => 'nullable|image|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'exists:event_tags,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();
    if ($request->has('title')) {
      $data['slug'] = Str::slug($request->title);
    }

    if ($request->hasFile('poster_image')) {
      $path = $request->file('poster_image')->store('event-posters', 'public');
      $data['poster_image'] = $path;
    }

    $event->update($data);

    if ($request->has('tags')) {
      $event->tags()->sync($request->tags);
    }

    return response()->json($event);
  }

  public function destroy(Request $request, Event $event)
  {
    if ($request->user()->id !== $event->promotor_id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $event->delete();
    return response()->json(null, 204);
  }

  public function register(Request $request, Event $event)
  {
    if ($event->registration_end < now()) {
      return response()->json(['message' => 'Registration period has ended'], 400);
    }

    if ($event->max_attendees && $event->attendees()->count() >= $event->max_attendees) {
      return response()->json(['message' => 'Event is full'], 400);
    }

    $attendee = $event->attendees()->create([
      'user_id' => $request->user()->id,
      'status' => $event->is_free ? 'registered' : 'pending_payment',
    ]);

    if (!$event->is_free) {
      // Handle payment creation here
      // This will be implemented with Midtrans integration
    }

    return response()->json($attendee, 201);
  }
}
