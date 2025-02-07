@extends('_layouts.master')

@section('body')

    <h2>Variables</h2>

    <div class="m-xs-y-4 border-b">
        <p>Local (title): {{ $page->title }}</p>
        <p>Local (author): {{ $page->author }}</p>
        <p>Meta (filename): {{ $page->getFilename() }}</p>

        <p>Helper function: {{ $page->helperFunction(15) }}</p>

        <p>Illuminate Collection methods are available on config arrays (<code>sum</code>, for example): {{ $page->global_array->sum() }} or {{ $page->nested_array->sum('value') }} or {{ $page->nested_array->sum(function ($item) { return $item->value; }) }}</p>
        <p>Illuminate Higher Order Messages are available on config arrays (<code>sum</code>, for example): {{ $page->nested_array->sum->value }}</p>

    </div>

    <div class="m-xs-y-4 border-b">
        <p>Iterating variables when referenced as objects:</p>
        <ul>

        @foreach($page->global_array as $item)
            <li>Array item: {{ $item }}</li>
        @endforeach

        </ul>
    </div>

    <div class="m-xs-y-4 border-b">
        <p>Nested arrays are accessible as iterable objects below first level:</p>
        <ul>

        @foreach($page->nested_array as $array)
            <li>Array item: {{ $array->name }}
                <ul>

                @foreach($array as $item)
                    <li>{{ $item }}</li>
                @endforeach

                </ul>
            </li>
        @endforeach

        </ul>
    </div>

    @yield('the_section')

@endsection
