<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\with;
use App\Models\Announcement;

layout('layouts.client');

with(function () {
    return [
        'announcements' => Announcement::latest()->get(),
    ];
});

?>

<div class="page active" id="page-client-announcements">
  <div class="card-header" style="margin-bottom: 16px;">
    <div class="card-title">Announcements & Messages</div>
    <div class="card-sub">Stay updated with the latest from management</div>
  </div>

  <div style="display:flex; flex-direction:column; gap:16px;">
    @forelse($announcements as $ann)
      <div class="card" style="border-left: 4px solid {{ $ann->type === 'alert' ? 'var(--danger)' : ($ann->type === 'success' ? 'var(--success)' : 'var(--info)') }}">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; flex-wrap: wrap; gap: 4px;">
          <div style="font-weight:600; font-size:var(--fs-md); color:var(--text);">{{ $ann->title }}</div>
          <div style="font-size:10px; color:var(--text3);">{{ $ann->created_at->format('M j, Y H:i') }}</div>
        </div>
        <div style="color:var(--text2); line-height:1.6; font-size:var(--fs);">
          {{ $ann->content }}
        </div>
        <div style="margin-top:12px; display:flex; gap:8px;">
           <span class="badge {{ $ann->type === 'alert' ? 'badge-danger' : ($ann->type === 'success' ? 'badge-success' : 'badge-info') }}">
             {{ ucfirst($ann->type) }}
           </span>
        </div>
      </div>
    @empty
      <div class="card" style="text-align:center; padding:40px; color:var(--text3);">
        <div style="font-size:24px; margin-bottom:10px;">📣</div>
        <div>No announcements yet. Check back later!</div>
      </div>
    @endforelse
  </div>
</div>
