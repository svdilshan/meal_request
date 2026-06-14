<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // If the logged-in user is not a super admin, hide super_admin users
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('role', '!=', 'super_admin');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('epf_no', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $allowedRoles = auth()->user()->isSuperAdmin()
            ? ['user', 'admin', 'super_admin']
            : ['user', 'admin'];

        $validated = $request->validate([
            'epf_no' => ['required', 'string', 'max:20', 'unique:users'],
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in($allowedRoles)],
        ]);

        User::create($validated);

        return back()->with('success', 'User ' . $validated['name'] . ' created successfully.');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent non-super-admins from modifying super_admin accounts
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $allowedRoles = auth()->user()->isSuperAdmin()
            ? ['user', 'admin', 'super_admin']
            : ['user', 'admin'];

        $validated = $request->validate([
            'epf_no' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in($allowedRoles)],
        ]);

        $user->update($validated);

        return back()->with('success', 'User ' . $user->name . ' updated successfully.');
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent non-super-admins from modifying super_admin accounts
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return back()->with('success', 'Password reset successfully for ' . $user->name . '.');
    }

    /**
     * Toggle active status of a user.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Prevent non-super-admins from modifying super_admin accounts
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent self-deactivation
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Import users from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        try {
            $import = new UsersImport(auth()->user()->isSuperAdmin());
            Excel::import($import, $request->file('file'));

            return back()->with('success', "Import completed: {$import->createdCount} created, {$import->updatedCount} updated, {$import->failedCount} failed.");
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Import failed. Please check the Excel format matches the template: EPF No, Name, Username, Password. Details: ' . $e->getMessage()]);
        }
    }

    /**
     * Download the Excel import template.
     */
    public function template()
    {
        $headers = [
            'EPF No',
            'Name',
            'Username',
            'Password',
        ];
        
        $data = [
            ['00001', 'John Silva', 'john.silva', 'Password123'],
            ['00002', 'Jane Perera', 'jane.perera', 'SecurePass456'],
        ];

        $export = new class($headers, $data) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            protected $data;
            public function __construct($headers, $data) {
                $this->headers = $headers;
                $this->data = $data;
            }
            public function array(): array {
                return array_merge([$this->headers], $this->data);
            }
        };

        $filename = 'user_import_template.xlsx';
        $tempPath = 'temp/' . $filename;

        Excel::store($export, $tempPath, 'local');

        $fullPath = storage_path('app/private/' . $tempPath);

        if (!file_exists($fullPath)) {
            abort(404, 'Template file not generated');
        }

        if (app()->environment('testing')) {
            return response()->download($fullPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));

        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($fullPath);
        exit;
    }
}
