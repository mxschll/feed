<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Stream;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Libraries\Feed as FeedParser;

class StreamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $streams = Stream::withCount('feeds')->having('feeds_count', '>', 2)->take(5)->get();
        return view('stream.index', compact('streams'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'stream_uuid' => 'sometimes|exists:streams,uuid',
        ]);

        // Check if url is valid RSS or Atom feed
        $feed = new FeedParser($request->url);
        try {
            $feed->parse();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Invalid feed URL');
        }

        $feed = Feed::firstOrNew([
            'url' => $feed->url,
            'title' => $feed->title,
            'domain' => $feed->domain,
        ]);
        $feed->save();

        if ($request->stream_uuid) {
            $stream = Stream::where('uuid', $request->stream_uuid)->first();
            $feeds = $stream->feeds;
            $feed_ids = $feeds->pluck('id');

            $feed_ids = ($feed_ids->push($feed->id))->unique();

            // Check if feed combination already exists
            $stream = Stream::whereHas('feeds', function ($query) use ($feed_ids) {
                $query->whereIn('feeds.id', $feed_ids);
            })->whereHas('feeds', function ($query) use ($feed_ids) {
                $query->whereIn('feeds.id', $feed_ids);
            }, '=', count($feed_ids))->first();

            if (!$stream) {
                $stream = new Stream();
                $stream->uuid = Str::uuid();
                $stream->save();
                $stream->feeds()->attach($feed_ids);
            }
        } else {
            $stream = new Stream();
            $stream->uuid = Str::uuid();
            $stream->save();
            $stream->feeds()->attach($feed->id);
        }

        return redirect()->route('stream.show', ['stream_uuid' => $stream->uuid]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($stream_uuid)
    {
        $stream = Stream::where('uuid', $stream_uuid)->firstOrFail();
        $feeds = $stream->feeds;

        $items = [];
        foreach ($feeds as $feed) {
            $items = array_merge($items, $feed->getEntries());
        }

        usort($items, function ($a, $b) {
            return $b->published->timestamp - $a->published->timestamp;
        });

        return view('stream.show', compact('items', 'stream'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
