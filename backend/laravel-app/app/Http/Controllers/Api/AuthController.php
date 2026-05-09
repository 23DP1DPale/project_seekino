<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nickname' => ['required', 'string', 'max:50', 'unique:users,nickname'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user = User::create([
            'nickname' => $request->input('nickname'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'user',
        ]);

        $plainToken = $this->createToken($user);

        return response()->json([
            'ziņa' => 'Reģistrācija veiksmīga.',
            'lietotājs' => $this->userResponse($user),
            'tokens' => [
                'access_token' => $plainToken,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], $this->messages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'ziņa' => 'E-pasts vai parole nav pareiza.',
            ], 401);
        }

        $plainToken = $this->createToken($user);

        return response()->json([
            'ziņa' => 'Pieslēgšanās veiksmīga.',
            'lietotājs' => $this->userResponse($user),
            'tokens' => [
                'access_token' => $plainToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $token = $this->tokenFromRequest($request);

        if (! $token) {
            return $this->unauthenticatedResponse();
        }

        $apiToken = $this->findApiToken($token);

        if (! $apiToken) {
            return $this->unauthenticatedResponse();
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        return response()->json([
            'ziņa' => 'Lietotāja dati iegūti veiksmīgi.',
            'lietotājs' => $this->userResponse($apiToken->user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $this->tokenFromRequest($request);

        if (! $token) {
            return $this->unauthenticatedResponse();
        }

        $apiToken = $this->findApiToken($token);

        if (! $apiToken) {
            return $this->unauthenticatedResponse();
        }

        $apiToken->delete();

        return response()->json([
            'ziņa' => 'Atslēgšanās veiksmīga.',
        ]);
    }

    private function createToken(User $user): string
    {
        $plainToken = Str::random(80);

        ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
        ]);

        return $plainToken;
    }

    private function tokenFromRequest(Request $request): ?string
    {
        $token = $request->bearerToken();

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function findApiToken(string $plainToken): ?ApiToken
    {
        return ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();
    }

    /**
     * @return array{id:int,nickname:string,email:string,role:string}
     */
    private function userResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'ziņa' => 'Ievadītie dati nav derīgi.',
            'kļūdas' => $errors,
        ], 422);
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'ziņa' => 'Autentifikācijas tokens nav derīgs vai nav norādīts.',
        ], 401);
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'nickname.required' => 'Lietotājvārds ir obligāts.',
            'nickname.max' => 'Lietotājvārds nedrīkst pārsniegt 50 rakstzīmes.',
            'nickname.unique' => 'Šāds lietotājvārds jau ir reģistrēts.',
            'email.required' => 'E-pasts ir obligāts.',
            'email.email' => 'E-pastam jābūt derīgai e-pasta adresei.',
            'email.max' => 'E-pasts nedrīkst pārsniegt 100 rakstzīmes.',
            'email.unique' => 'Šāds e-pasts jau ir reģistrēts.',
            'password.required' => 'Parole ir obligāta.',
            'password.min' => 'Parolei jābūt vismaz 8 rakstzīmes garai.',
            'password.confirmed' => 'Paroles apstiprinājums nesakrīt.',
        ];
    }
}
