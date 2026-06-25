@extends('layouts.admin')

@section('content')
<div class="page active" id="page-add-user">
  <div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
      <div class="card-title">Add New User</div>
      <a href="{{ route('users') }}" class="btn btn-outline btn-sm" wire:navigate>Back to Users</a>
    </div>

    @if ($errors->any())
      <div class="alert-banner danger">
        <ul style="margin-left: 1rem;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
      @csrf
      <div class="grid-2" style="gap: 16px;">
        <!-- Left Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Full Name *</label>
            <input type="text" name="name" class="filter-input" style="width:100%" required value="{{ old('name') }}">
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Email Address</label>
            <input type="email" name="email" class="filter-input" style="width:100%" value="{{ old('email') }}">
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Phone Number *</label>
            <input type="text" name="phone" class="filter-input" style="width:100%" required value="{{ old('phone') }}">
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Alternate Phone</label>
            <input type="text" name="phoneOne" class="filter-input" style="width:100%" value="{{ old('phoneOne') }}">
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Initial Password *</label>
            <input type="password" name="password" class="filter-input" style="width:100%" required>
          </div>
        </div>

        <!-- Right Column -->
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Role *</label>
            <select name="role" class="filter-input" style="width:100%" required>
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Status</label>
            <select name="status" class="filter-input" style="width:100%">
                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Country</label>
            <input type="text" name="country" class="filter-input" style="width:100%" value="{{ old('country') }}">
          </div>
          <div class="grid-2">
            <div>
              <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">City</label>
              <input type="text" name="city" class="filter-input" style="width:100%" value="{{ old('city') }}">
            </div>
            <div>
              <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">State/Region</label>
              <input type="text" name="state" class="filter-input" style="width:100%" value="{{ old('state') }}">
            </div>
          </div>
          <div>
            <label style="font-size:var(--fs-sm);color:var(--text3);margin-bottom:4px;display:block;">Zip/Postal Code</label>
            <input type="text" name="zip" class="filter-input" style="width:100%" value="{{ old('zip') }}">
          </div>
        </div>
      </div>
      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); text-align: right;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: var(--fs-md);">Save User</button>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('topbar-title').innerText = "Add New User";
  document.getElementById('topbar-sub').innerText = "Create a new member profile";
</script>
@endsection
