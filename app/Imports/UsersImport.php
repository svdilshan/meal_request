<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToArray, WithHeadingRow
{
    public $createdCount = 0;
    public $updatedCount = 0;
    public $failedCount = 0;
    protected $isSuperAdminImporter;

    public function __construct(bool $isSuperAdminImporter = false)
    {
        $this->isSuperAdminImporter = $isSuperAdminImporter;
    }

    /**
     * Map headings to lowercase key format for easier access.
     */
    public function headingRow(): int
    {
        return 1;
    }

    /**
     * Process imported rows.
     */
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            // Find keys dynamically, support both standard headers and position
            $epfNo = $row['epf_no'] ?? $row['epf_number'] ?? null;
            $name = $row['name'] ?? null;
            $username = $row['username'] ?? null;
            $password = $row['password'] ?? null;

            // Fallback for indexing if headers are missing or mismatched
            if (is_null($epfNo)) {
                $values = array_values($row);
                if (count($values) >= 4) {
                    $epfNo = $values[0] ?? null;
                    $name = $values[1] ?? null;
                    $username = $values[2] ?? null;
                    $password = $values[3] ?? null;
                }
            }

            // Cleanup whitespace
            $epfNo = $epfNo ? trim((string)$epfNo) : null;
            $name = $name ? trim((string)$name) : null;
            $username = $username ? trim((string)$username) : null;
            $password = $password ? trim((string)$password) : null;

            if (empty($epfNo) || empty($name) || empty($username)) {
                $this->failedCount++;
                continue;
            }

            // Look up by EPF number
            $user = User::where('epf_no', $epfNo)->first();

            if ($user) {
                // Prevent non-super-admins from modifying super_admin accounts
                if ($user->isSuperAdmin() && !$this->isSuperAdminImporter) {
                    $this->failedCount++;
                    continue;
                }
                // Update user details
                $user->name = $name;
                $user->username = $username;
                if (!empty($password)) {
                    $user->password = $password; // will be hashed automatically by User model casts
                }
                $user->save();
                $this->updatedCount++;
            } else {
                // Create user
                if (empty($password)) {
                    $this->failedCount++;
                    continue; // password is required for new users
                }
                
                // Ensure username is unique
                if (User::where('username', $username)->exists()) {
                    $this->failedCount++;
                    continue;
                }

                User::create([
                    'epf_no' => $epfNo,
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'role' => 'user',
                    'is_active' => true,
                ]);
                $this->createdCount++;
            }
        }
    }
}
