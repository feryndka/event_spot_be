<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\Api\EventResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
  public function index(Request $request)
  {
    try {
      $query = Event::query()
        ->with(['promotor', 'category', 'images', 'tags'])
        ->when($request->search, function ($query, $search) {
          $query->search($search);
        })
        ->when($request->category_id, function ($query, $categoryId) {
          $query->byCategory($categoryId);
        })
        ->when($request->featured, function ($query) {
          $query->featured();
        })
        ->when($request->upcoming, function ($query) {
          $query->upcoming();
        })
        ->when($request->ongoing, function ($query) {
          $query->ongoing();
        })
        ->when($request->past, function ($query) {
          $query->past();
        })
        ->when($request->free, function ($query) {
          $query->free();
        })
        ->when($request->paid, function ($query) {
          $query->paid();
        });

      // If user is promotor and authenticated, only show their events
      if (Auth::check() && Auth::user()->user_type === 'promotor') {
        $query->where('promotor_id', Auth::id());
      }

      $events = $query->latest()->paginate(10);

      if (request()->segment(1) == 'api') return EventResource::collection($events);
      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in event index: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load events',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function store(Request $request)
  {
    try {
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
        'is_free' => 'boolean',
        'price' => 'nullable|numeric|min:0',
        'max_attendees' => 'nullable|integer|min:1',
        'poster_image' => 'nullable|image|max:2048',
        'images.*' => 'nullable|image|max:2048',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:event_tags,id'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $validator->validated();
      $data['slug'] = Str::slug($data['title']);
      $data['promotor_id'] = Auth::id();
      $data['is_published'] = false; // Default to unpublished
      $data['is_approved'] = false; // Default to unapproved

      // Handle poster image upload
      if ($request->hasFile('poster_image')) {
        $data['poster_image'] = $request->file('poster_image')->store('events/posters', 'public');
      }

      $event = Event::create($data);

      // Handle additional images
      if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
          $path = $image->store('events/images', 'public');
          $event->images()->create([
            'image_path' => $path,
            'image_type' => 'additional',
            'is_primary' => false,
            'order' => $event->images()->count() + 1
          ]);
        }
      }

      // Handle tags
      if ($request->has('tags')) {
        $event->tags()->sync($request->tags);
      }

      return new EventResource($event->load(['promotor', 'category', 'images', 'tags']));
    } catch (\Exception $e) {
      Log::error('Error in event store: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create event'
      ], 500);
    }
  }

  public function show(Event $event)
  {
    try {
      // Load relationships
      $event->load(['promotor', 'category', 'images', 'tags']);

      // Only load statistics if user is promotor or admin
      if (Auth::check() && (Auth::user()->user_type === 'promotor' || Auth::user()->user_type === 'admin')) {
        $event->load('statistics');
      }

      // Increment views count
      $event->increment('views_count');

      return new EventResource($event);
    } catch (\Exception $e) {
      Log::error('Error in event show: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load event details',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function update(Request $request, Event $event)
  {
    try {
      // Check if user is the promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $validator = Validator::make($request->all(), [
        'title' => 'string|max:255',
        'description' => 'string',
        'category_id' => 'exists:categories,id',
        'location_name' => 'string|max:255',
        'address' => 'string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'start_date' => 'date',
        'end_date' => 'date|after:start_date',
        'registration_start' => 'date',
        'registration_end' => 'date|after:registration_start|before:start_date',
        'is_free' => 'boolean',
        'price' => 'nullable|numeric|min:0',
        'max_attendees' => 'nullable|integer|min:1',
        'poster_image' => 'nullable|image|max:2048',
        'images.*' => 'nullable|image|max:2048',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:event_tags,id'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $validator->validated();

      // Update slug if title is changed
      if (isset($data['title'])) {
        $data['slug'] = Str::slug($data['title']);
      }

      // Handle poster image upload
      if ($request->hasFile('poster_image')) {
        // Delete old poster
        if ($event->poster_image) {
          Storage::disk('public')->delete($event->poster_image);
        }
        $data['poster_image'] = $request->file('poster_image')->store('events/posters', 'public');
      }

      $event->update($data);

      // Handle additional images
      if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
          $path = $image->store('events/images', 'public');
          $event->images()->create([
            'image_path' => $path,
            'image_type' => 'additional',
            'is_primary' => false,
            'order' => $event->images()->count() + 1
          ]);
        }
      }

      // Handle tags
      if ($request->has('tags')) {
        $event->tags()->sync($request->tags);
      }

      return new EventResource($event->load(['promotor', 'category', 'images', 'tags']));
    } catch (\Exception $e) {
      Log::error('Error in event update: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update event'
      ], 500);
    }
  }

  public function destroy(Event $event)
  {
    try {
      // Check if user is the promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      // Delete poster image
      if ($event->poster_image) {
        Storage::disk('public')->delete($event->poster_image);
      }

      // Delete additional images
      foreach ($event->images as $image) {
        Storage::disk('public')->delete($image->image_path);
      }

      $event->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Event deleted successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in event destroy: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete event'
      ], 500);
    }
  }

  public function publish(Event $event)
  {
    try {
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $event->update(['is_published' => true]);

      return new EventResource($event->load(['promotor', 'category', 'images', 'tags']));
    } catch (\Exception $e) {
      Log::error('Error in event publish: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to publish event'
      ], 500);
    }
  }

  public function unpublish(Event $event)
  {
    try {
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $event->update(['is_published' => false]);

      return new EventResource($event->load(['promotor', 'category', 'images', 'tags']));
    } catch (\Exception $e) {
      Log::error('Error in event unpublish: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to unpublish event'
      ], 500);
    }
  }

  public function getStatistics(Event $event)
  {
    try {
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      return response()->json([
        'status' => 'success',
        'data' => [
          'views' => $event->views_count,
          'registrations' => $event->registrations()->count(),
          'favorites' => $event->favorites()->count(),
          'shares' => $event->shares()->count(),
          'comments' => $event->comments()->count(),
          'reviews' => $event->reviews()->count(),
          'average_rating' => $event->averageRating(),
          'total_revenue' => $event->payments()->where('status', 'completed')->sum('amount')
        ]
      ]);
    } catch (\Exception $e) {
      Log::error('Error in event statistics: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get event statistics'
      ], 500);
    }
  }

  public function getAttendees(Event $event)
  {
    try {
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $attendees = $event->registrations()
        ->with('user')
        ->latest()
        ->paginate(10);

      return response()->json([
        'status' => 'success',
        'data' => $attendees
      ]);
    } catch (\Exception $e) {
      Log::error('Error in event attendees: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get event attendees'
      ], 500);
    }
  }

  public function getPayments(Event $event)
  {
    try {
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $payments = $event->payments()
        ->with('user')
        ->latest()
        ->paginate(10);

      return response()->json([
        'status' => 'success',
        'data' => $payments
      ]);
    } catch (\Exception $e) {
      Log::error('Error in event payments: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get event payments'
      ], 500);
    }
  }

  /**
   * Search events with various filters
   */
  public function search(Request $request)
  {
    try {
      $query = Event::query()
        ->with(['promotor', 'category', 'images', 'tags']);
      // ->where('is_published', true)
      // ->where('is_approved', true)
      // Search by title or description
      if ($request->has('q')) {
        $searchTerm = $request->q;
        $query->where(function ($q) use ($searchTerm) {
          $q->where('title', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%");
        });
      }

      // Filter by category
      if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
      }

      // Filter by date range
      if ($request->has('start_date')) {
        $query->where('start_date', '>=', $request->start_date);
      }
      if ($request->has('end_date')) {
        $query->where('end_date', '<=', $request->end_date);
      }

      // Filter by price
      if ($request->has('is_free')) {
        $query->where('is_free', $request->is_free);
      }
      if ($request->has('min_price')) {
        $query->where('price', '>=', $request->min_price);
      }
      if ($request->has('max_price')) {
        $query->where('price', '<=', $request->max_price);
      }

      // Filter by tags
      if ($request->has('tags')) {
        $tags = explode(',', $request->tags);
        $query->whereHas('tags', function ($q) use ($tags) {
          $q->whereIn('id', $tags);
        });
      }

      $events = $query->latest()->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in event search: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to search events',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  /**
   * Search events by location
   */
  public function byLocation(Request $request, $location)
  {
    try {
      $query = Event::query()
        ->with(['promotor', 'category', 'images', 'tags'])
        // ->where('is_published', true)
        // ->where('is_approved', true)
        ->where(function ($q) use ($location) {
          $q->where('location_name', 'like', "%{$location}%")
            ->orWhere('address', 'like', "%{$location}%");
        });

      $events = $query->latest()->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in location search: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to search events by location',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  /**
   * Search events by date
   */
  public function byDate(Request $request, $date)
  {
    try {
      $query = Event::query()
        ->with(['promotor', 'category', 'images', 'tags'])
        // ->where('is_published', true)
        // ->where('is_approved', true)
        ->whereDate('start_date', $date);

      $events = $query->latest()->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in date search: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to search events by date',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  /**
   * Search events nearby
   */
  public function nearby(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'radius' => 'numeric|min:1|max:100' // radius in kilometers
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $latitude = $request->latitude;
      $longitude = $request->longitude;
      $radius = $request->radius ?? 10; // default 10km

      // Haversine formula to calculate distance
      $query = Event::query()
        ->with(['promotor', 'category', 'images', 'tags'])
        ->where('is_published', true)
        ->where('is_approved', true)
        ->whereRaw("
          (6371 * acos(
            cos(radians(?)) * 
            cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * 
            sin(radians(latitude))
          )) <= ?
        ", [$latitude, $longitude, $latitude, $radius]);

      $events = $query->latest()->paginate(10);

      return EventResource::collection($events);
    } catch (\Exception $e) {
      Log::error('Error in nearby search: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to search nearby events',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }
}
