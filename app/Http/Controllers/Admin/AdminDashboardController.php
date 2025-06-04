<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Statistic;
use App\Models\Category;
use App\Models\EventAttendee;
use App\Models\Bookmark;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function statistics(Request $request)
    {
        try {
            // Define time period for filtering (default: last 30 days)
            $period = $request->period ?? 30;
            $startDate = Carbon::now()->subDays($period);

            // Get total counts
            $totalUsers = User::count();
            $totalEvents = Event::count();
            $totalCategories = Category::count();

            // Get user counts by type
            $usersByType = User::select('user_type', DB::raw('count(*) as count'))
                ->groupBy('user_type')
                ->pluck('count', 'user_type')
                ->toArray();

            // Get active events count
            $activeEvents = Event::where('end_date', '>=', now())
                ->where('is_published', 1)
                ->count();

            // Get events by category
            $eventsByCategory = Event::select('categories.name', DB::raw('count(*) as count'))
                ->join('categories', 'events.category_id', '=', 'categories.id')
                ->groupBy('categories.name')
                ->pluck('count', 'categories.name')
                ->toArray();

            // Get new users in the last period
            $newUsers = User::where('created_at', '>=', $startDate)
                ->count();

            // Get new events in the last period
            $newEvents = Event::where('created_at', '>=', $startDate)
                ->count();

            // Get most viewed events
            $mostViewedEvents = Event::orderBy('views_count', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'views_count']);

            // Get engagement statistics
            $engagementStats = Statistic::select(
                    DB::raw('DATE(data_date) as date'),
                    DB::raw('SUM(page_views) as total_views'),
                    DB::raw('SUM(unique_visitors) as total_visitors'),
                    DB::raw('AVG(engagement_rate) as avg_engagement'),
                    DB::raw('AVG(click_through_rate) as avg_ctr')
                )
                ->where('data_date', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_users' => $totalUsers,
                    'total_events' => $totalEvents,
                    'total_categories' => $totalCategories,
                    'users_by_type' => $usersByType,
                    'active_events' => $activeEvents,
                    'events_by_category' => $eventsByCategory,
                    'new_users' => $newUsers,
                    'new_events' => $newEvents,
                    'most_viewed_events' => $mostViewedEvents,
                    'engagement_stats' => $engagementStats,
                    'period' => $period
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in dashboard statistics: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard statistics',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function detailedStatistics(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // User growth over time
            $userGrowth = User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as new_users')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Event growth over time
            $eventGrowth = Event::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as new_events')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Daily engagement metrics
            $dailyEngagement = Statistic::select(
                    'data_date as date',
                    DB::raw('SUM(page_views) as total_views'),
                    DB::raw('SUM(unique_visitors) as total_visitors'),
                    DB::raw('AVG(engagement_rate) as avg_engagement'),
                    DB::raw('AVG(click_through_rate) as avg_ctr')
                )
                ->whereBetween('data_date', [$startDate, $endDate])
                ->groupBy('data_date')
                ->orderBy('data_date')
                ->get();

            // Category popularity
            $categoryPopularity = Event::select(
                    'categories.name as category_name',
                    DB::raw('COUNT(events.id) as event_count'),
                    DB::raw('SUM(events.views_count) as total_views')
                )
                ->join('categories', 'events.category_id', '=', 'categories.id')
                ->whereBetween('events.created_at', [$startDate, $endDate])
                ->groupBy('categories.name')
                ->orderBy('total_views', 'desc')
                ->get();

            // User activity by type
            $userActivity = User::select(
                    'user_type',
                    DB::raw('COUNT(*) as user_count'),
                    DB::raw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_users')
                )
                ->setBindings([$startDate, $endDate])
                ->groupBy('user_type')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_growth' => $userGrowth,
                    'event_growth' => $eventGrowth,
                    'daily_engagement' => $dailyEngagement,
                    'category_popularity' => $categoryPopularity,
                    'user_activity' => $userActivity,
                    'date_range' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                        'days' => $endDate->diffInDays($startDate) + 1
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detailed statistics: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load detailed statistics',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function eventStatistics(Request $request)
    {
        try {
            // Define time period for filtering (default: last 30 days)
            $period = $request->period ?? 30;
            $startDate = Carbon::now()->subDays($period);

            // Get total events count
            $totalEvents = Event::count();

            // Get published vs unpublished events
            $publishedEvents = Event::where('is_published', true)->count();
            $unpublishedEvents = $totalEvents - $publishedEvents;

            // Get approved vs unapproved events
            $approvedEvents = Event::where('is_approved', true)->count();
            $unapprovedEvents = $totalEvents - $approvedEvents;

            // Get free vs paid events
            $freeEvents = Event::where('is_free', true)->count();
            $paidEvents = $totalEvents - $freeEvents;

            // Get events by status (upcoming, ongoing, past)
            $upcomingEvents = Event::where('start_date', '>', now())->count();
            $ongoingEvents = Event::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count();
            $pastEvents = Event::where('end_date', '<', now())->count();

            // Get events created in the specified period
            $newEvents = Event::where('created_at', '>=', $startDate)->count();

            // Get events by category
            $eventsByCategory = Event::select('categories.name', DB::raw('count(*) as count'))
                ->join('categories', 'events.category_id', '=', 'categories.id')
                ->groupBy('categories.name')
                ->orderBy('count', 'desc')
                ->get();

            // Get most viewed events
            $mostViewedEvents = Event::orderBy('views_count', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'views_count', 'start_date', 'end_date']);

            // Get events with highest attendance
            $highestAttendance = Event::select('events.id', 'events.title', DB::raw('COUNT(event_attendees.id) as attendee_count'))
                ->leftJoin('event_attendees', 'events.id', '=', 'event_attendees.event_id')
                ->groupBy('events.id', 'events.title')
                ->orderBy('attendee_count', 'desc')
                ->limit(10)
                ->get();

            // Get events by month
            $eventsByMonth = Event::select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    $date = Carbon::createFromDate($item->year, $item->month, 1);
                    return [
                        'month' => $date->format('F'),
                        'year' => $item->year,
                        'count' => $item->count,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_events' => $totalEvents,
                    'published_vs_unpublished' => [
                        'published' => $publishedEvents,
                        'unpublished' => $unpublishedEvents,
                    ],
                    'approved_vs_unapproved' => [
                        'approved' => $approvedEvents,
                        'unapproved' => $unapprovedEvents,
                    ],
                    'free_vs_paid' => [
                        'free' => $freeEvents,
                        'paid' => $paidEvents,
                    ],
                    'events_by_status' => [
                        'upcoming' => $upcomingEvents,
                        'ongoing' => $ongoingEvents,
                        'past' => $pastEvents,
                    ],
                    'new_events' => $newEvents,
                    'events_by_category' => $eventsByCategory,
                    'most_viewed_events' => $mostViewedEvents,
                    'highest_attendance' => $highestAttendance,
                    'events_by_month' => $eventsByMonth,
                    'period' => $period
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in event statistics: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load event statistics',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function userStatistics(Request $request)
    {
        try {
            // Define time period for filtering (default: last 30 days)
            $period = $request->period ?? 30;
            $startDate = Carbon::now()->subDays($period);

            // Get total users count
            $totalUsers = User::count();

            // Get users by type
            $usersByType = User::select('user_type', DB::raw('count(*) as count'))
                ->groupBy('user_type')
                ->pluck('count', 'user_type')
                ->toArray();

            // Get users by status
            $usersByStatus = User::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get new users in the specified period
            $newUsers = User::where('created_at', '>=', $startDate)->count();

            // Get new users by day in the specified period
            $newUsersByDay = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get most active users (by event attendance)
            $mostActiveByAttendance = User::select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(event_attendees.id) as attendance_count')
                )
                ->leftJoin('event_attendees', 'users.id', '=', 'event_attendees.user_id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('attendance_count', 'desc')
                ->limit(10)
                ->get();

            // Get most active users (by comments)
            $mostActiveByComments = User::select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(comments.id) as comment_count')
                )
                ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('comment_count', 'desc')
                ->limit(10)
                ->get();

            // Get most active users (by bookmarks)
            $mostActiveByBookmarks = User::select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(bookmarks.id) as bookmark_count')
                )
                ->leftJoin('bookmarks', 'users.id', '=', 'bookmarks.user_id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('bookmark_count', 'desc')
                ->limit(10)
                ->get();

            // Get users by month
            $usersByMonth = User::select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    $date = Carbon::createFromDate($item->year, $item->month, 1);
                    return [
                        'month' => $date->format('F'),
                        'year' => $item->year,
                        'count' => $item->count,
                    ];
                });

            // Get promotor verification stats
            $promotorStats = [
                'total' => User::where('user_type', 'promotor')->count(),
                'verified' => User::where('user_type', 'promotor')->where('is_verified', true)->count(),
                'unverified' => User::where('user_type', 'promotor')->where('is_verified', false)->count(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_users' => $totalUsers,
                    'users_by_type' => $usersByType,
                    'users_by_status' => $usersByStatus,
                    'new_users' => $newUsers,
                    'new_users_by_day' => $newUsersByDay,
                    'most_active_by_attendance' => $mostActiveByAttendance,
                    'most_active_by_comments' => $mostActiveByComments,
                    'most_active_by_bookmarks' => $mostActiveByBookmarks,
                    'users_by_month' => $usersByMonth,
                    'promotor_stats' => $promotorStats,
                    'period' => $period
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in user statistics: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load user statistics',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
