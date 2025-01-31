---
author: Keith Damiani
---

@extends('_layouts.master')

@section('body')
<h2>Collection Test</h2>

    <div>
        <h3>Higher order messages test for collections</h3>
        <p>Total of numbers in collection: {{ $collection_tests->sum('number') }}</p>
        <p>Total of numbers in collection: {{ $collection_tests->sum->number }}</p>
    </div>

    <hr>

    <h3>Helper function: {{ $page->helperFunction() }}</h3>

    @foreach ($collection_tests as $item)

    <p>Total of numbers in collection: {{ $item->sum }}</p>


    Helper function, invoked at collection-item level:
    <h4>{{ $item->helperFunction() }}</h4>

    <div class="row">
        <div class="col-xs-12">
            <h3><a href="{{ $item->getUrl() }}">{{ $item['title'] }}</a></h3>

            <p class="text-sm">by {{ $item->author }} · Number {{ $item->number }}</p>

            <p class="p-xs-b-6 border-b">{!! $item->globalPreview(180) !!}...</p>
        </div>
    </div>
    @endforeach

@endsection
