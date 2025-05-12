<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use App\Models\EventAttendee;
use App\Models\EventImage;
use App\Models\EventTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
  public function index(Request $request)
  {
    $query = Event::where('promotor_id', $request->user()->id)
      ->with(['category', 'attendees', 'statistics']);

    // Filter by status
    if ($request->has('status')) {
      switch ($request->status) {
        case 'published':
          $query->published();
          break;
        case 'draft':
          $query->where('is_published', false);
          break;
        case 'featured':
          $query->featured();
          break;
        case 'approved':
          $query->approved();
          break;
      }
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

    $events = $query->latest()->paginate(10);
    return response()->json($events);
  }

  public function show(Event $event)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $event->load([
      'category',
      'attendees.user',
      'comments.user',
      'statistics',
      'tags',
      'images'
    ]);

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
      'poster_image' => 'required|image|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'string',
      'images' => 'nullable|array',
      'images.*' => 'image|max:2048',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();
    $data['slug'] = Str::slug($request->title);
    $data['promotor_id'] = $request->user()->id;

    // Upload poster image
    if ($request->hasFile('poster_image')) {
      $path = $request->file('poster_image')->store('event-posters', 'public');
      $data['poster_image'] = $path;
    }

    $event = Event::create($data);

    // Handle tags
    if ($request->has('tags')) {
      foreach ($request->tags as $tagName) {
        $tag = EventTag::firstOrCreate(['name' => $tagName]);
        $event->tags()->attach($tag->id);
      }
    }

    // Handle additional images
    if ($request->hasFile('images')) {
      foreach ($request->file('images') as $image) {
        $path = $image->store('event-images', 'public');
        $event->images()->create(['image_path' => $path]);
      }
    }

    $event->load(['category', 'tags', 'images']);
    return response()->json($event, 201);
  }

  public function update(Request $request, Event $event)
  {
    if ($event->promotor_id !== $request->user()->id) {
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
      'poster_image' => 'sometimes|required|image|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'string',
      'images' => 'nullable|array',
      'images.*' => 'image|max:2048',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();
    if ($request->has('title')) {
      $data['slug'] = Str::slug($request->title);
    }

    // Update poster image
    if ($request->hasFile('poster_image')) {
      // Delete old poster
      if ($event->poster_image) {
        Storage::disk('public')->delete($event->poster_image);
      }
      $path = $request->file('poster_image')->store('event-posters', 'public');
      $data['poster_image'] = $path;
    }

    $event->update($data);

    // Handle tags
    if ($request->has('tags')) {
      $event->tags()->detach();
      foreach ($request->tags as $tagName) {
        $tag = EventTag::firstOrCreate(['name' => $tagName]);
        $event->tags()->attach($tag->id);
      }
    }

    // Handle additional images
    if ($request->hasFile('images')) {
      foreach ($request->file('images') as $image) {
        $path = $image->store('event-images', 'public');
        $event->images()->create(['image_path' => $path]);
      }
    }

    $event->load(['category', 'tags', 'images']);
    return response()->json($event);
  }

  public function destroy(Event $event)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete poster image
    if ($event->poster_image) {
      Storage::disk('public')->delete($event->poster_image);
    }

    // Delete additional images
    foreach ($event->images as $image) {
      Storage::disk('public')->delete($image->image_path);
      $image->delete();
    }

    $event->delete();
    return response()->json(null, 204);
  }

  public function publish(Event $event)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $event->update(['is_published' => true]);
    return response()->json($event);
  }

  public function unpublish(Event $event)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $event->update(['is_published' => false]);
    return response()->json($event);
  }

  public function attendees(Event $event)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $attendees = $event->attendees()
      ->with(['user', 'payment'])
      ->latest()
      ->paginate(10);

    return response()->json($attendees);
  }

  public function checkIn(Request $request, Event $event, EventAttendee $attendee)
  {
    if ($event->promotor_id !== request()->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    if ($attendee->event_id !== $event->id) {
      return response()->json(['message' => 'Attendee not found for this event'], 404);
    }

    $attendee->update([
      'status' => 'attended',
      'check_in_time' => now()
    ]);

    return response()->json($attendee);
  }

  public function generateDescription(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
    ]);

    $title = $request->input('title');

    $prompt = "Buat deskripsi event yang menarik dan informatif berdasarkan judul berikut:\n\nJudul: \"$title\"\n\nDeskripsi:";

    $response = Http::withToken(env('OPENAI_API_KEY'))
      ->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo', // Can be changed to gpt-4 or other models
        'messages' => [
          ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
        'max_tokens' => 150,
      ]);

    $description = $response->json('choices.0.message.content');

    return response()->json([
      'title' => $title,
      'description' => trim($description),
    ]);
  }
}
