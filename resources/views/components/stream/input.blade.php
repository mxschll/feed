<form method="POST" action="/stream">
    @csrf
    <input @if($errors->any()) aria-invalid="true" @endif type="text" name="url" placeholder="Add RSS or Atom feed">
    @if($errors->any())
    {{ implode('', $errors->all(':message')) }}
    @endif
    @isset($stream)
    <input type="hidden" name="stream_uuid" value="{{ $stream->uuid }}" />
    @endisset
</form>
@isset($stream)
<small>
    @foreach ($stream->feeds()->pluck('domain') as $domain)
    {{ $domain }} @if (!$loop->last) • @endif
    @endforeach
</small>
@endisset