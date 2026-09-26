<h2>Listing rejected</h2>
<p>Hi {{ $user->name }},</p>
<p>Your listing <strong>{{ $vehicle->title }}</strong> was rejected.</p>
<p><strong>Reason:</strong> {{ $reason }}</p>
<p>
  <a href="{{ $editUrl }}">Edit and resubmit</a>
</p>

