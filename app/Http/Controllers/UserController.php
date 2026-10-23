<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserAccountService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private UserAccountService $accounts)
    {
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(User::validRoles())],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = $filters['search'] ?? '';
        $role = $filters['role'] ?? '';
        $status = $filters['status'] ?? '';

        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => compact('search', 'role', 'status'),
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form', ['user' => null]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated() + ['is_active' => true]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff account created. Share the password with them securely.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Form', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->accounts->assertCanChangeRole($user, $data['role'], $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['role' => $e->getMessage()])->withInput();
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is_active) {
            try {
                $this->accounts->assertCanDeactivate($user, $request->user());
            } catch (DomainException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): RedirectResponse
    {
        // The model's 'hashed' cast hashes the password on save.
        $user->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password reset.');
    }
}
