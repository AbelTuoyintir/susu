<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_access_control()
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme', 'plan' => 'free']);
        Tenant::setTenantId($tenant->id);

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $admin = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $user = User::factory()->create(['role' => 'user', 'tenant_id' => $tenant->id]);

        // 1. Regular user gets 403 or redirect
        $this->actingAs($user);
        $response = $this->get('/super-admin');
        $response->assertStatus(403);

        // 2. Admin gets 403
        $this->actingAs($admin);
        $response = $this->get('/super-admin');
        $response->assertStatus(403);

        // 3. Super Admin gets 200 OK
        $this->actingAs($superAdmin);
        $response = $this->get('/super-admin');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_modify_tenant_plan_and_status()
    {
        $tenant = Tenant::create(['name' => 'Acme Inc', 'slug' => 'acme', 'plan' => 'free']);
        Tenant::setTenantId($tenant->id);

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $this->actingAs($superAdmin);

        // Test toggleStatus via Livewire Volt component
        Volt::test('pages.super-admin.dashboard')
            ->call('toggleStatus', $tenant->id);

        $tenant->refresh();
        $this->assertEquals('inactive', $tenant->status);

        // Test editPlan and savePlan
        Volt::test('pages.super-admin.dashboard')
            ->call('editPlan', $tenant->id)
            ->set('newPlan', 'premium')
            ->call('savePlan');

        $tenant->refresh();
        $this->assertEquals('premium', $tenant->plan);
    }

    public function test_suspended_tenant_blocks_regular_user_access()
    {
        $tenant = Tenant::create(['name' => 'Suspended Inc', 'slug' => 'suspended', 'plan' => 'free', 'status' => 'inactive']);
        Tenant::setTenantId($tenant->id);

        $user = User::factory()->create(['role' => 'user', 'tenant_id' => $tenant->id]);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        // Regular user should get suspended/403 block on any page
        $this->actingAs($user);
        $response = $this->get(route('client.dashboard'));
        $response->assertStatus(403);
        $this->assertStringContainsString('suspended', $response->getContent());

        // Super Admin can bypass the suspension block
        $this->actingAs($superAdmin);
        $response = $this->get('/super-admin');
        $response->assertStatus(200);
    }
}
