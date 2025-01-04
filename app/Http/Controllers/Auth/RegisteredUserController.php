<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\GroupService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        // Generate a unique userId based on email
        $userId = $this->generateUniqueUserId($request->email);
        $randomColor = $this->generateRandomColor();
        $randomBio = $this->generateRandomBio();

        $profile = $user->profile()->create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'is_online' => true,
            'last_activity' => now(),
            'avatarColor' => $randomColor,
            'userId' => $userId,
            'bio' => $randomBio,
        ]);

        $user->load('profile');

        // Dispatch the registered event
        event(new Registered($user));

        // Handle group creation or joining
        $this->groupService->createOrJoinGroup($user);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * Generate a unique userId based on the user's email.
     *
     * @param string $email
     * @return string
     */
    private function generateUniqueUserId(string $email): string
    {
        // Extract the part before the @ symbol
        $baseUserId = strtolower(explode('@', $email)[0]);

        // Sanitize the userId (remove non-alphanumeric characters)
        $baseUserId = preg_replace('/[^a-z0-9]/', '', $baseUserId);

        // Initialize the unique userId
        $uniqueUserId = $baseUserId;
        $counter = 1;

        // Check for uniqueness and append a counter if necessary
        while ($this->userIdExists($uniqueUserId)) {
            $uniqueUserId = $baseUserId . $counter;
            $counter++;
        }

        return $uniqueUserId;
    }

    /**
     * Check if a userId already exists in the user_profiles table.
     *
     * @param string $userId
     * @return bool
     */
    private function userIdExists(string $userId): bool
    {
        return UserProfile::where('userId', $userId)->exists();
    }

    private function generateRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    private function generateRandomBio(): string
    {
        $bios = [
            "Dream big!",
            "Stay positive.",
            "Love to learn.",
            "Keep it simple.",
            "Work hard.",
            "Be kind.",
            "Adventure awaits.",
            "Making memories.",
            "Chasing dreams.",
            "Hello, world!",
            "Smile more.",
            "Think different.",
            "Stay curious.",
            "Life is good.",
            "Be yourself."
        ];

        return $bios[array_rand($bios)];
    }
}
