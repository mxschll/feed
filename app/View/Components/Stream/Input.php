<?php

namespace App\View\Components\Stream;

use Illuminate\View\Component;

class Input extends Component
{

    /**
     * Strean model object.
     */
    public $stream;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($stream = null)
    {
        $this->stream = $stream;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.stream.input');
    }
}
