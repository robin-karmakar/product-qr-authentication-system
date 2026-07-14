@extends('layouts.app')

@section('title', 'Product Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Product Categories</h4>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" id="openCreateModalBtn">
                + New Category
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Back to Users</a>
        </div>
    </div>

    <div id="alertBox"></div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr data-uuid="{{ $category->uuid }}">
                            <td class="cat-name">{{ $category->name }}</td>
                            <td class="text-muted">{{ $category->slug }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button
                                    class="btn btn-sm btn-outline-primary edit-category-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#categoryModal"
                                    data-uuid="{{ $category->uuid }}"
                                    data-name="{{ $category->name }}"
                                    data-description="{{ $category->description }}"
                                    data-is-active="{{ $category->is_active ? 1 : 0 }}"
                                >
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-category-btn" data-uuid="{{ $category->uuid }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categoryForm">
                    <input type="hidden" name="uuid" id="categoryUuid">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="categoryName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (optional)</label>
                            <textarea name="description" id="categoryDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="categoryIsActive" class="form-check-input" checked>
                            <label for="categoryIsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
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
    const form = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('categoryModalTitle');
    const uuidField = document.getElementById('categoryUuid');
    const errorsBox = document.getElementById('formErrors');
    const categoryModalEl = document.getElementById('categoryModal');

    function showAlert(message, type) {
        alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

    function resetForm() {
        form.reset();
        uuidField.value = '';
        modalTitle.textContent = 'New Category';
        errorsBox.classList.add('d-none');
        errorsBox.innerHTML = '';
        document.getElementById('categoryIsActive').checked = true;
    }

    document.getElementById('openCreateModalBtn').addEventListener('click', resetForm);

    document.querySelectorAll('.edit-category-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            resetForm();
            modalTitle.textContent = 'Edit Category';
            uuidField.value = btn.dataset.uuid;
            document.getElementById('categoryName').value = btn.dataset.name;
            document.getElementById('categoryDescription').value = btn.dataset.description === 'null' ? '' : btn.dataset.description;
            document.getElementById('categoryIsActive').checked = btn.dataset.isActive === '1';
        });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorsBox.classList.add('d-none');
        errorsBox.innerHTML = '';

        const uuid = uuidField.value;
        const isEdit = Boolean(uuid);
        const url = isEdit ? `/admin/categories/${uuid}` : '{{ route('admin.categories.store') }}';

        const payload = {
            name: document.getElementById('categoryName').value,
            description: document.getElementById('categoryDescription').value,
            is_active: document.getElementById('categoryIsActive').checked,
        };

        try {
            const response = await fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
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
                } else {
                    showAlert(data.message || 'Something went wrong.', 'danger');
                }
                return;
            }

            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(categoryModalEl).hide();
            setTimeout(() => window.location.reload(), 800);
        } catch (err) {
            showAlert('An unexpected error occurred. Please try again.', 'danger');
        }
    });

    document.querySelectorAll('.delete-category-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Delete this category? This cannot be undone.')) {
                return;
            }

            const uuid = btn.dataset.uuid;

            try {
                const response = await fetch(`/admin/categories/${uuid}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    showAlert(data.message || 'Unable to delete this category.', 'danger');
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
