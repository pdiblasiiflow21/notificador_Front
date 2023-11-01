<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Requests\Users\CreateUserRequest;
use App\Models\User;
use App\Service\V1\UserService;
use Illuminate\Http\Request;

class UsersController extends ApiController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $response = $this->userService->index($request);

        return response()->json($response, $response['code']);
    }

    public function store(CreateUserRequest $request)
    {
        $data     = $request->validated();
        $response = $this->userService->store($data);

        return response()->json(['message' => $response['message'], 'user' => $response['user'] ?? null], $response['code']);
    }

    public function update(Request $request, User $user)
    {
        $data     = $request->all();
        $response = $this->userService->update($user, $data);

        return response()->json(['message' => $response['message'], 'user' => $response['user'] ?? null], $response['code']);
    }

    public function toggleStatus($id)
    {
        $response = $this->userService->toggleStatus((int) $id);

        return response()->json(['message' => $response['message'], 'user' => $response['user'] ?? null], $response['code']);
    }
}
