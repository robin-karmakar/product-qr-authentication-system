@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Staff Accounts</h4>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                + Create Staff Account
            </button>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Log Out</button>
            </form>
        </div>
    </div>

    <div id="alertBox"></div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-uuid="{{ $user->uuid }}">
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-secondary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }} status-badge">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if ($user->is_active)
                                    <button class="btn btn-sm btn-outline-danger toggle-status-btn" data-action="deactivate" data-uuid="{{ $user->uuid }}">
                                        Deactivate
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-success toggle-status-btn" data-action="activate" data-uuid="{{ $user->uuid }}">
                                        Activate
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="createUserForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Staff Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled selected>Select a role</option>
                                @foreach ($assignableRoles as $role)
                                    <option value="{{ $role }}">{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const alertBox = document.getElementById('alertBox');

    function showAlert(message, type) {
        alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    document.getElementById('createUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const errorsBox = document.getElementById('formErrors');
        errorsBox.classList.add('d-none');
        errorsBox.innerHTML = '';

        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const response = await fetch('{{ route('admin.users.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    errorsBox.innerHTML = Object.values(data.errors).flat().join('<br>');
                    errorsBox.classList.remove('d-none');
                }
                return;
            }

            showAlert(data.message, 'success');
            form.reset();
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
            setTimeout(() => window.location.reload(), 900);
        } catch (err) {
            showAlert('An unexpected error occurred. Please try again.', 'danger');
        }
    });

    document.querySelectorAll('.toggle-status-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const uuid = btn.dataset.uuid;
            const action = btn.dataset.action;
            const url = `/admin/users/${uuid}/${action}`;

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.message || 'Action failed.', 'danger');
                    return;
                }

                showAlert(data.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                showAlert('An unexpected error occurred. Please try again.', 'danger');
            }
        });
    });
})();
</script>
@endpush
