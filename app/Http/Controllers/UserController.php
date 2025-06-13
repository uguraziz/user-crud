<?php

namespace App\Http\Controllers;

use App\Filters\FuzzyFilter;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        return QueryBuilder::for(User::class)
        ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'country', 'gender'])
        ->allowedFilters([
            AllowedFilter::beginsWithStrict('first_name'),
            AllowedFilter::beginsWithStrict('last_name'),
            AllowedFilter::beginsWithStrict('email'),
            AllowedFilter::beginsWithStrict('phone'),
            AllowedFilter::beginsWithStrict('country'),
            AllowedFilter::exact('gender'),
        ])
        ->defaultSort('-id')
        ->simplePaginate($perPage);
    }

    public function count()
    {
        return User::count();
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create($validatedData);

        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user)
        ], 201);

    }

    public function update(UserUpdateRequest $request, User $user)
    {

        $validatedData = $request->validated();

        $user->update($validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user)
        ]);

    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => "User deleted successfully"
        ]);
    }
}
