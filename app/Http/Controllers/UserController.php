<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();

        return new UserCollection($users);
    }

    public function store(UserStoreRequest $request)
    {
        $payload = $request->validated();

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => bcrypt($request->password),
            'role' => $payload['role'],
            'birth_date' => $payload['birth_date'],

        ]);

        return new UserResource($user);
    }

    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request, User $user)
    {

        $payload = $request->validated();
        Log::info('PAYLOAD UPDATE USER', $payload);

        $user->update($payload);

        if ($request->password) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        return new UserResource($user);
    }
}
