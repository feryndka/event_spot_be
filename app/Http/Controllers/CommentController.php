<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Api\CommentResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
  /**
   * Get all comments for an event
   */
  public function index(Request $request, Event $event)
  {
    try {
      $comments = $event->comments()
        ->with(['user', 'replies.user'])
        ->whereNull('parent_id')
        ->latest()
        ->paginate(10);

      return CommentResource::collection($comments);
    } catch (\Exception $e) {
      Log::error('Error in comment index: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load comments'
      ], 500);
    }
  }

  /**
   * Store a new comment
   */
  public function store(Request $request, Event $event)
  {
    try {
      $validator = Validator::make($request->all(), [
        'content' => 'required|string|max:1000',
        'parent_id' => 'nullable|exists:comments,id'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = $request->user();
      $data = $validator->validated();

      // Check if parent comment belongs to the same event
      if (isset($data['parent_id'])) {
        $parentComment = Comment::find($data['parent_id']);
        if ($parentComment->event_id !== $event->id) {
          return response()->json([
            'status' => 'error',
            'message' => 'Parent comment does not belong to this event'
          ], 422);
        }
      }

      $comment = $event->comments()->create([
        'user_id' => $user->id,
        'content' => $data['content'],
        'parent_id' => $data['parent_id'] ?? null
      ]);

      return new CommentResource($comment->load(['user', 'replies.user']));
    } catch (\Exception $e) {
      Log::error('Error in comment store: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create comment'
      ], 500);
    }
  }

  /**
   * Update a comment
   */
  public function update(Request $request, Comment $comment)
  {
    try {
      // Check if user owns the comment
      if ($comment->user_id !== $request->user()->id) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $validator = Validator::make($request->all(), [
        'content' => 'required|string|max:1000'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $comment->update($validator->validated());

      return new CommentResource($comment->load(['user', 'replies.user']));
    } catch (\Exception $e) {
      Log::error('Error in comment update: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update comment'
      ], 500);
    }
  }

  /**
   * Delete a comment
   */
  public function destroy(Request $request, Comment $comment)
  {
    try {
      // Check if user owns the comment
      if ($comment->user_id !== $request->user()->id) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized'
        ], 403);
      }

      $comment->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Comment deleted successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in comment destroy: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete comment'
      ], 500);
    }
  }

  /**
   * Get user's comment statistics
   */
  public function getStatistics(Request $request)
  {
    try {
      $user = $request->user();

      $statistics = [
        'total_comments' => $user->comments()->count(),
        'total_replies' => $user->comments()->whereNotNull('parent_id')->count(),
        'total_events_commented' => $user->comments()->distinct('event_id')->count(),
        'recent_activity' => $user->comments()
          ->with(['event', 'replies'])
          ->latest()
          ->take(5)
          ->get()
      ];

      return response()->json([
        'status' => 'success',
        'data' => $statistics
      ]);
    } catch (\Exception $e) {
      Log::error('Error in getStatistics: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get comment statistics'
      ], 500);
    }
  }

  /**
   * Get admin-level comment statistics
   */
  public function getAdminStatistics(Request $request)
  {
    try {
      // Verify admin role
      if ($request->user()->user_type !== 'admin') {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized. Only admin can access this endpoint.'
        ], 403);
      }

      $statistics = [
        'total_comments' => Comment::count(),
        'total_replies' => Comment::whereNotNull('parent_id')->count(),
        'total_events_with_comments' => Comment::distinct('event_id')->count(),
        'recent_comments' => CommentResource::collection(
          Comment::with(['user', 'event', 'replies'])
            ->latest()
            ->take(10)
            ->get()
        ),
        'comments_by_user_type' => [
          'user' => Comment::whereHas('user', function ($query) {
            $query->where('user_type', 'user');
          })->count(),
          'promotor' => Comment::whereHas('user', function ($query) {
            $query->where('user_type', 'promotor');
          })->count()
        ]
      ];

      return response()->json([
        'status' => 'success',
        'data' => $statistics
      ]);
    } catch (\Exception $e) {
      Log::error('Error in getAdminStatistics: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get admin statistics'
      ], 500);
    }
  }

  /**
   * Get event-specific comment statistics
   */
  public function getEventCommentStatistics(Request $request, Event $event)
  {
    try {
      // Verify admin role
      if ($request->user()->user_type !== 'admin') {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized. Only admin can access this endpoint.'
        ], 403);
      }

      $statistics = [
        'total_comments' => $event->comments()->whereNull('parent_id')->count(),
        'total_replies' => $event->comments()->whereNotNull('parent_id')->count(),
        'unique_commenters' => $event->comments()->distinct('user_id')->count(),
        'comments_by_user_type' => [
          'user' => $event->comments()->whereHas('user', function ($query) {
            $query->where('user_type', 'user');
          })->count(),
          'promotor' => $event->comments()->whereHas('user', function ($query) {
            $query->where('user_type', 'promotor');
          })->count()
        ],
        'recent_comments' => CommentResource::collection(
          $event->comments()
            ->with(['user', 'replies'])
            ->latest()
            ->take(5)
            ->get()
        )
      ];

      return response()->json([
        'status' => 'success',
        'data' => $statistics
      ]);
    } catch (\Exception $e) {
      Log::error('Error in getEventCommentStatistics: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get event comment statistics'
      ], 500);
    }
  }

  /**
   * Admin delete comment
   */
  public function deleteComment(Request $request, Comment $comment)
  {
    try {
      // Check if user is admin
      if ($request->user()->user_type !== 'admin') {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized. Only admin can delete comments.'
        ], 403);
      }

      // Delete the comment and its replies
      $comment->replies()->delete();
      $comment->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Comment and its replies deleted successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in admin delete comment: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete comment'
      ], 500);
    }
  }
}
