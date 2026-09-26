<h2>New contact message</h2>
<p><strong>Name:</strong> {{ $name }}</p>
<p><strong>Email:</strong> {{ $email }}</p>
@if(!empty($phone))
  <p><strong>Phone:</strong> {{ $phone }}</p>
@endif
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($message)) !!}</p>

