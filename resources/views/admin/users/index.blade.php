@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="row">
    <!-- Header -->
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">User Management</h2>
            <p class="text-muted mb-0">Create, update, activate, and import employee accounts.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i> Add Single User
            </button>
            <button class="btn btn-accent btn-sm px-3" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel Upload
            </button>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="col-12">
        <div class="app-card bg-white mb-4">
            <div class="card-body p-4">
                <!-- Search bar -->
                <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, username, or EPF number..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    </div>
                </form>

                <!-- Users list -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>EPF No</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td class="fw-bold">{{ $u->epf_no }}</td>
                                    <td>{{ $u->name }}</td>
                                    <td><code>{{ $u->username }}</code></td>
                                    <td>
                                        @if($u->role === 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($u->role === 'admin')
                                            <span class="badge bg-primary">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($u->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-patch-check-fill me-1"></i> Active</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1"><i class="bi bi-slash-circle-fill me-1"></i> Deactivated</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Details -->
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-id="{{ $u->id }}"
                                                    data-name="{{ $u->name }}"
                                                    data-username="{{ $u->username }}"
                                                    data-epf="{{ $u->epf_no }}"
                                                    data-role="{{ $u->role }}">
                                                <i class="bi bi-pencil" title="Edit details"></i>
                                            </button>
                                            
                                            <!-- Reset Password -->
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-reset" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#resetPasswordModal"
                                                    data-id="{{ $u->id }}"
                                                    data-name="{{ $u->name }}">
                                                <i class="bi bi-key" title="Reset Password"></i>
                                            </button>

                                            <!-- Toggle status -->
                                            <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @if($u->is_active)
                                                    <button type="submit" class="btn btn-outline-danger" title="Deactivate" {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                                                        <i class="bi bi-shield-x"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-outline-success" title="Activate">
                                                        <i class="bi bi-shield-check"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No users found matching the filter criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination links -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New Employee User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_epf_no" class="form-label small fw-bold">EPF Number</label>
                        <input type="text" name="epf_no" id="new_epf_no" class="form-control" placeholder="e.g. 1001" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_name" class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" id="new_name" class="form-control" placeholder="John Silva" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_username" class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" id="new_username" class="form-control" placeholder="john.silva" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-bold">Initial Password</label>
                        <input type="password" name="password" id="new_password" class="form-control" placeholder="Min 6 characters" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_role" class="form-label small fw-bold">System Role</label>
                        <select name="role" id="new_role" class="form-select" required>
                            <option value="user" selected>Normal User</option>
                            <option value="admin">Administrator</option>
                            @if(auth()->user()->isSuperAdmin())
                                <option value="super_admin">Super Administrator</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit User Details -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_epf_no" class="form-label small fw-bold">EPF Number</label>
                        <input type="text" name="epf_no" id="edit_epf_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label small fw-bold">System Role</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="user">Normal User</option>
                            <option value="admin">Administrator</option>
                            @if(auth()->user()->isSuperAdmin())
                                <option value="super_admin">Super Administrator</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="resetPasswordModalLabel">Reset User Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">You are resetting the password for user: <strong id="reset_user_name"></strong></p>
                    <div class="mb-3">
                        <label for="reset_password" class="form-label small fw-bold">New Temporary Password</label>
                        <input type="password" name="password" id="reset_password" class="form-control" placeholder="Min 6 characters" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excel Import -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importExcelModalLabel">Import Users from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <h6 class="fw-bold mb-2 small"><i class="bi bi-info-circle text-primary me-1"></i> Excel Column Requirements:</h6>
                        <p class="small text-muted mb-1">Your excel file must contain a header row in the first sheet with columns:</p>
                        <table class="table table-sm table-bordered bg-white mb-0 small text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th>EPF No</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1001</td>
                                    <td>John Silva</td>
                                    <td>john.silva</td>
                                    <td>Pass@123</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                            <span class="small text-danger"><strong>Note:</strong> Password column can be empty for existing users.</span>
                            <a href="{{ route('admin.users.template') }}" class="btn btn-outline-success btn-sm px-3">
                                <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Template
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excel_file" class="form-label small fw-bold">Select Excel file (.xlsx, .xls)</label>
                        <input class="form-control" type="file" name="file" id="excel_file" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent btn-sm">Upload & Process</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Handle modal populate logic for Edit User details
    const editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const username = button.getAttribute('data-username');
            const epf = button.getAttribute('data-epf');
            const role = button.getAttribute('data-role');

            const form = editModal.querySelector('#editUserForm');
            form.setAttribute('action', `/admin/users/${id}/update`);

            editModal.querySelector('#edit_name').value = name;
            editModal.querySelector('#edit_username').value = username;
            editModal.querySelector('#edit_epf_no').value = epf;
            editModal.querySelector('#edit_role').value = role;
        });
    }

    // Handle modal populate logic for Password Reset
    const resetModal = document.getElementById('resetPasswordModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');

            const form = resetModal.querySelector('#resetPasswordForm');
            form.setAttribute('action', `/admin/users/${id}/reset`);

            resetModal.querySelector('#reset_user_name').textContent = name;
            resetModal.querySelector('#reset_password').value = '';
        });
    }
</script>
@endsection
