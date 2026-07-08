<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Announcement;
use App\Jobs\SendAnnouncementJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Livewire\Volt\Volt;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_announcement()
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
        $this->actingAs($admin);

        Queue::fake();

        Volt::test('pages.admin.notifications')
            ->set('title', 'Test Announcement')
            ->set('message', 'This is a test message')
            ->set('recipientGroup', 'All Users')
            ->call('sendAnnouncement');

        $this->assertDatabaseHas('announcements', [
            'title' => 'Test Announcement',
            'content' => 'This is a test message',
            'target_group' => 'All Users',
        ]);

        Queue::assertPushed(SendAnnouncementJob::class);
    }
}
