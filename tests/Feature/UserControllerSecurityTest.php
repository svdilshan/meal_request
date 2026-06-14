<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Imports\UsersImport;

class UserControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $admin;
    protected $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Super Admin
        $this->superAdmin = User::create([
            'epf_no' => '0001',
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create Admin
        $this->admin = User::create([
            'epf_no' => '0002',
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Normal User
        $this->normalUser = User::create([
            'epf_no' => '0003',
            'name' => 'Normal User',
            'username' => 'normal',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Test admin index excludes super admin.
     */
    public function test_admin_cannot_view_super_admin_users()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertDontSee($this->superAdmin->name);
        $response->assertDontSee($this->superAdmin->username);
        $response->assertSee($this->normalUser->name);
        $response->assertSee($this->admin->name);
    }

    /**
     * Test super admin index can see everyone.
     */
    public function test_super_admin_can_view_all_users()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee($this->superAdmin->name);
        $response->assertSee($this->normalUser->name);
        $response->assertSee($this->admin->name);
    }

    /**
     * Test admin cannot create super admin.
     */
    public function test_admin_cannot_create_super_admin_users()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'epf_no' => '0004',
            'name' => 'New Super',
            'username' => 'newsuper',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['username' => 'newsuper']);
    }

    /**
     * Test super admin can create super admin.
     */
    public function test_super_admin_can_create_super_admin_users()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'epf_no' => '0004',
            'name' => 'New Super',
            'username' => 'newsuper',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['username' => 'newsuper', 'role' => 'super_admin']);
    }

    /**
     * Test admin cannot update super admin.
     */
    public function test_admin_cannot_update_super_admin_users()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.update', $this->superAdmin->id), [
            'epf_no' => '0001',
            'name' => 'Super Admin Updated',
            'username' => 'superadmin',
            'role' => 'super_admin',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('Super Admin', $this->superAdmin->fresh()->name);
    }

    /**
     * Test super admin can update super admin.
     */
    public function test_super_admin_can_update_super_admin_users()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.update', $this->superAdmin->id), [
            'epf_no' => '0001',
            'name' => 'Super Admin Updated',
            'username' => 'superadmin',
            'role' => 'super_admin',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Super Admin Updated', $this->superAdmin->fresh()->name);
    }

    /**
     * Test admin cannot reset super admin password.
     */
    public function test_admin_cannot_reset_super_admin_password()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.reset', $this->superAdmin->id), [
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin cannot toggle super admin status.
     */
    public function test_admin_cannot_toggle_super_admin_status()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle', $this->superAdmin->id));

        $response->assertStatus(403);
        $this->assertTrue($this->superAdmin->fresh()->is_active);
    }

    /**
     * Test admin can toggle standard user status.
     */
    public function test_admin_can_toggle_standard_user_status()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle', $this->normalUser->id));

        $response->assertSessionHasNoErrors();
        $this->assertFalse($this->normalUser->fresh()->is_active);

        // Toggle back
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle', $this->normalUser->id));
        $response->assertSessionHasNoErrors();
        $this->assertTrue($this->normalUser->fresh()->is_active);
    }

    /**
     * Test admin cannot update a super admin via Excel import.
     */
    public function test_admin_cannot_update_super_admin_via_import()
    {
        $rows = [
            [
                'epf_no' => '0001',
                'name' => 'Hacked Name',
                'username' => 'superadmin',
                'password' => 'newpassword123',
            ]
        ];

        $import = new UsersImport(false);
        $import->array($rows);

        $this->assertEquals(0, $import->updatedCount);
        $this->assertEquals(1, $import->failedCount);
        $this->assertEquals('Super Admin', $this->superAdmin->fresh()->name);
    }

    /**
     * Test super admin can update a super admin via Excel import.
     */
    public function test_super_admin_can_update_super_admin_via_import()
    {
        $rows = [
            [
                'epf_no' => '0001',
                'name' => 'Updated Super Admin Name',
                'username' => 'superadmin',
                'password' => 'newpassword123',
            ]
        ];

        $import = new UsersImport(true);
        $import->array($rows);

        $this->assertEquals(1, $import->updatedCount);
        $this->assertEquals(0, $import->failedCount);
        $this->assertEquals('Updated Super Admin Name', $this->superAdmin->fresh()->name);
    }
}
