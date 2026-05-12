<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $users = User::query()
            ->orderBy('id')
            ->get()
            ->map(fn (User $user): array => $this->userResponse($user))
            ->values();

        return response()->json([
            'ziņa' => 'Lietotāji iegūti veiksmīgi.',
            'users' => $users,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validator = Validator::make($request->all(), [
            'nickname' => ['required', 'string', 'max:50', Rule::unique('users', 'nickname')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'admin'])],
        ], $this->messages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        if (
            $admin->id === $user->id &&
            $user->role === 'admin' &&
            $validated['role'] === 'user'
        ) {
            return response()->json([
                'ziņa' => 'Administrators nevar pats sev noņemt administratora tiesības.',
            ], 422);
        }

        $user->update($validated);

        return response()->json([
            'ziņa' => 'Lietotājs veiksmīgi atjaunināts.',
            'user' => $this->userResponse($user->refresh()),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        if ($admin->id === $user->id) {
            return response()->json([
                'ziņa' => 'Administrators nevar izdzēst pats sevi.',
            ], 422);
        }

        DB::transaction(function () use ($user): void {
            $reservationIds = DB::table('reservations')
                ->where('user', $user->id)
                ->pluck('id');

            DB::table('tickets')
                ->whereIn('reservation', $reservationIds)
                ->delete();
            DB::table('reservations_seats')
                ->whereIn('reservation', $reservationIds)
                ->delete();
            DB::table('reservations')
                ->whereIn('id', $reservationIds)
                ->delete();

            $user->delete();
        });

        return response()->json([
            'ziņa' => 'Lietotājs veiksmīgi dzēsts.',
        ]);
    }

    private function adminUser(Request $request): User|JsonResponse
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            return $this->unauthenticatedResponse();
        }

        $apiToken = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $apiToken || ! $apiToken->user) {
            return $this->unauthenticatedResponse();
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        if ($apiToken->user->role !== 'admin') {
            return response()->json([
                'ziņa' => 'Tev nav tiesību pārvaldīt lietotājus.',
            ], 403);
        }

        return $apiToken->user;
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
            'nickname.required' => 'Segvārds ir obligāts.',
            'nickname.string' => 'Segvārdam jābūt tekstam.',
            'nickname.max' => 'Segvārds nedrīkst pārsniegt 50 rakstzīmes.',
            'nickname.unique' => 'Šāds segvārds jau ir reģistrēts.',
            'email.required' => 'E-pasts ir obligāts.',
            'email.string' => 'E-pastam jābūt tekstam.',
            'email.email' => 'E-pastam jābūt derīgai e-pasta adresei.',
            'email.max' => 'E-pasts nedrīkst pārsniegt 100 rakstzīmes.',
            'email.unique' => 'Šāds e-pasts jau ir reģistrēts.',
            'role.required' => 'Loma ir obligāta.',
            'role.in' => 'Lomai jābūt user vai admin.',
        ];
    }
}
