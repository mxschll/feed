<nav aria-label="breadcrumb">
    @if(!Route::is('stream.index') )
    <ul>
        <li><a href="{{ route('stream.index') }}">Home</a></li>
        <li>Stream</li>
    </ul>
    @endif
</nav>
