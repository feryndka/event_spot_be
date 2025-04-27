<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
  public function index(Event $event)
  {
    $comments = $event->comments()
      ->with(['user', 'replies.user'])
      ->whereNull('parent_id')
      ->approved()
      ->latest()
      ->paginate(10);

    return response()->json($comments);
  }

  public function store(Request $request, Event $event)
  {
    $validator = Validator::make($request->all(), [
      'content' => 'required|string|max:1000',
      'parent_id' => 'nullable|exists:comments,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $comment = $event->comments()->create([
      'user_id' => $request->user()->id,
      'content' => $request->content,
      'parent_id' => $request->parent_id,
    ]);

    $comment->load('user');

    return response()->json($comment, 201);
  }

  public function update(Request $request, Comment $comment)
  {
    if ($request->user()->id !== $comment->user_id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
      'content' => 'required|string|max:1000',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $comment->update(['content' => $request->content]);
    return response()->json($comment);
  }

  public function destroy(Request $request, Comment $comment)
  {
    if ($request->user()->id !== $comment->user_id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $comment->delete();
    return response()->json(null, 204);
  }

  public function approve(Comment $comment)
  {
    $comment->update(['is_approved' => true]);
    return response()->json($comment);
  }

  public function reject(Comment $comment)
  {
    $comment->update(['is_approved' => false]);
    return response()->json($comment);
  }
}
