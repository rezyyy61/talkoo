<?php

namespace App\Http\Controllers;

use App\Events\FriendRequest;
use App\Events\FriendRequestUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FriendResource;


class FriendshipController extends Controller
{
    /**
     * Search for users by name or email.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('query');

        // Validate the search query
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid search query.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Search users excluding the authenticated user
        $users = User::with('profile')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%$query%")
                    ->orWhere('email', 'LIKE', "%$query%");
            })
            ->where('id', '!=', Auth::id())
            ->paginate(10, ['id', 'name', 'email']);

        return response()->json($users, 200);
    }

    /**
     * Send a friend request to a user.
     */
    public function sendRequest(Request $request): JsonResponse
    {
        $recipientId = $request->input('friend_id');
        $validator = Validator::make($request->all(), [
            'friend_id' => 'required|exists:users,id|different:user_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid friend ID.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();

        if ($userId == $recipientId) {
            return response()->json([
                'message' => 'You cannot send a friend request to yourself.',
            ], 400);
        }

        $existingFriendship = Friendship::where(function ($q) use ($userId, $recipientId) {
            $q->where('user_id', $userId)->where('friend_id', $recipientId);
        })->orWhere(function ($q) use ($userId, $recipientId) {
            $q->where('user_id', $recipientId)->where('friend_id', $userId);
        })->first();

        if ($existingFriendship) {
            if ($existingFriendship->status === 'pending') {
                return response()->json(['message' => 'Friend request already pending.'], 400);
            } elseif ($existingFriendship->status === 'accepted') {
                return response()->json(['message' => 'You are already friends.'], 400);
            } elseif ($existingFriendship->status === 'declined') {
                $existingFriendship->status = 'pending';
                $existingFriendship->save();
                return response()->json(['message' => 'Friend request re-sent.'], 200);
            }
        }

        // Create a new friendship request
        $friendship = Friendship::create([
            'user_id' => $userId,
            'friend_id' => $recipientId,
            'status' => 'pending',
        ]);

        broadcast(new FriendRequest($friendship))->toOthers();

        return response()->json([
            'message' => 'Friend request sent successfully.',
        ], 201);
    }


    /**
     * Accept a friend request.
     */
    public function acceptRequest(Request $request): JsonResponse
    {
        $friendshipId = $request->input('friendship_id');

        // Validate the friendship ID
        $validator = Validator::make($request->all(), [
            'friendship_id' => 'required|exists:friendships,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid friendship ID.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $friendship = Friendship::find($friendshipId);

        // Check if the authenticated user is the recipient of the request
        if ($friendship->friend_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        if ($friendship->status !== 'pending') {
            return response()->json([
                'message' => 'Friend request is not pending.',
            ], 400);
        }

        // Update the friendship status to accepted
        $friendship->status = 'accepted';
        $friendship->save();

        broadcast(new FriendRequestUpdated($friendship, 'accepted'))->toOthers();

        return response()->json([
            'message' => 'Friend request accepted.',
        ], 200);
    }

    /**
     * Decline a friend request.
     */
    public function declineRequest(Request $request): JsonResponse
    {
        $friendshipId = $request->input('friendship_id');

        // Validate the friendship ID
        $validator = Validator::make($request->all(), [
            'friendship_id' => 'required|exists:friendships,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid friendship ID.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $friendship = Friendship::find($friendshipId);

        // Check if the authenticated user is the recipient of the request
        if ($friendship->friend_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        if ($friendship->status !== 'pending') {
            return response()->json([
                'message' => 'Friend request is not pending.',
            ], 400);
        }

        // Update the friendship status to declined
        $friendship->status = 'declined';
        $friendship->save();

        broadcast(new FriendRequestUpdated($friendship, 'declined'))->toOthers();

        return response()->json([
            'message' => 'Friend request declined.',
        ], 200);
    }

    /**
     * List all friends and friend requests with their statuses.
     */
    public function listFriends(): JsonResponse
    {
        $user = Auth::user();

        // Retrieve accepted friends
        $friends = $user->friends()->with('profile')->get(['users.id', 'users.name', 'users.email']);

        // Retrieve sent friend requests
        $sentRequests = $user->friendRequestsSent()
            ->with('profile')
            ->select('friendships.id as friendship_id', 'users.id', 'users.name', 'users.email')
            ->get();

        // Retrieve received friend requests
        $receivedRequests = $user->friendRequestsReceived()
            ->with('profile')
            ->select('friendships.id as friendship_id', 'users.id', 'users.name', 'users.email')
            ->get();

        $data = [
            'friends' => $friends,
            'sent_requests' => $sentRequests,
            'received_requests' => $receivedRequests,
        ];

        return response()->json($data, 200);
    }

    /**
     * List all accepted friends.
     */
    /**
     * List the authenticated user and all accepted friends.
     */
    public function listFriendsAccepted()
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $userId = Auth::id();

        // Retrieve accepted friendships and include profiles
        $friends = Friendship::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('friend_id', $userId);
        })
            ->where('status', 'accepted')
            ->with([
                'user.profile',
                'friend.profile'
            ])
            ->get();

        // Map the relationships to ensure the correct user is returned
        $friendsList = $friends->map(function ($friendship) use ($userId) {
            return $friendship->user_id === $userId ? $friendship->friend : $friendship->user;
        });

        return response()->json([
            'friends' => $friendsList
        ], 200);
    }



    /**
     * Get friendship statuses between the authenticated user and multiple users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFriendshipStatuses(Request $request): JsonResponse
    {
        $friendIds = $request->input('friend_ids', []);

        // Validate the input
        $validator = Validator::make($request->all(), [
            'friend_ids'    => 'required|array|max:100', // Limit to 100 for performance
            'friend_ids.*'  => 'integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();

        // Fetch all relevant friendships
        $friendships = Friendship::where(function ($query) use ($userId, $friendIds) {
            $query->where('user_id', $userId)
                ->whereIn('friend_id', $friendIds);
        })
            ->orWhere(function ($query) use ($userId, $friendIds) {
                $query->where('friend_id', $userId)
                    ->whereIn('user_id', $friendIds);
            })
            ->get(['user_id', 'friend_id', 'status']);

        $statuses = [];

        foreach ($friendships as $friendship) {
            if ($friendship->user_id === $userId) {
                $statuses[$friendship->friend_id] = $friendship->status;
            } else {
                $statuses[$friendship->user_id] = $friendship->status;
            }
        }

        // Assign 'none' status if no friendship exists
        foreach ($friendIds as $id) {
            if (!isset($statuses[$id])) {
                $statuses[$id] = 'none';
            }
        }

        return response()->json($statuses, 200);
    }

    public function getSelectedUserWithProfile($id)
    {
        $user = User::with('profile')->find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
}
