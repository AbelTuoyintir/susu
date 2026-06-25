<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['member_id' => 'MEM-001', 'role' => 'admin']);

    $component = Volt::test('pages.auth.login')
        ->set('form.member_id', 'MEM-001')
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create(['member_id' => 'MEM-002']);

    $component = Volt::test('pages.auth.login')
        ->set('form.member_id', 'MEM-002')
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // No navigation volt component in this specific layout, it uses blades.
    // We can simulate logout by posting to logout route
    $response = $this->post('/logout');

    $response->assertRedirect('/');

    $this->assertGuest();
});
