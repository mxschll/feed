<?php

namespace App\Models;

use App\Libraries\Feed as FeedParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'title',
        'domain'
    ];

    public function getEntries() {

        $feed = new FeedParser($this->url);
        $feed->parse();
        return $feed->entries;
    }
}
