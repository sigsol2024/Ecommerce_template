<h2>Listing submitted for approval</h2>
<p><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</p>
<p><strong>Vehicle:</strong> {{ $vehicle->title }}</p>
<p><strong>Status:</strong> {{ $vehicle->status }}</p>
<p>
  <a href="{{ $adminUrl }}">Open admin moderation</a>
</p>

