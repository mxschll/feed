<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stream</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}">
</head>

<body>
    <header>
        <hgroup>
            <h1>The Feed Agregator</h1>
            <h2>No scripts, no ads, no tracking. No login required.</h2>
        </hgroup>
        <x-stream.input />
    </header>

    <main>
        <section>
            <h3>What is this?</h3>
            <p>
                This is a feed aggregator that doesn't use any scripts, ads, tracking, or login. It's just a simple aggregator that you can use to get an <i>overview</i> of your favorite blogs and news sites without any of the usual annoyances. It does not replace full fledged RSS readers.
            </p>

            <h3>How does it work?</h3>
            <p>
                Enter the URL of a RSS or Atom feed and you're good to go. You can add as many feeds as you want. The collection of multiple feeds is called stream. Here are some examples to start with:
            </p>

            <ul>
                @foreach ($streams as $stream)
                <li><a href="/{{ $stream->uuid }}">{{ implode(', ', $stream->feeds()->pluck('domain')->toArray()) }}</a></li>
                @endforeach
            </ul>
        </section>
    </main>

    @include('components.global.footer')
</body>

</html>
