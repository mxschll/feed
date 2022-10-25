<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Feed Aggregator</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}">
</head>

<body>

    @include('components.stream.navigation')

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

    @include('components.global.footer')
</body>

</html>
