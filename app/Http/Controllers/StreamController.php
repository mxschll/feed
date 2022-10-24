<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StreamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $streams = Stream::all();
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

        $feed = Feed::firstOrNew([
            'url' => $request->url,
            'domain' => parse_url($request->url, PHP_URL_HOST),
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
