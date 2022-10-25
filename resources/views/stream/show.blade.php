@extends('layouts.default')

@section('content')
<header>
    <x-stream.input :stream="$stream" />
</header>

<main>
    @foreach ($items as $item)
    <article>
        <header><a href="{{ $item->url }}"><b>{{ $item->title }}</b></a></header>
        <small>{{ $item->domain }} @if($item->published) • {{ $item->published->format('Y-m-d H:i') }}@endif</small>
        <x-markdown>{{ $item->content }}</x-markdown>
    </article>
    @endforeach
</main>
@endsection