---
author: Keith Damiani
---

@extends('_layouts.master')

@section('body')
<h2>Posts</h2>

    <h3>Helper function: {{ $page->helperFunction() }}</h3>

    @foreach ($posts as $post)

    Helper function, invoked at collection-item level:
    <h4>{{ $post->helperFunction() }}</h4>

    <div class="row">
        <div class="col-xs-12">
            <h3><a href="{{ $post->getUrl() }}">{{ $post['title'] }}</a></h3>

            <p class="text-sm">by {{ $post->author }} · {{ $post->date_formatted() }} · Number {{ $post->number }}</p>

            <p class="p-xs-b-6 border-b">{!! $post->preview(180) !!}...</p>
        </div>
    </div>
    @endforeach

@endsection
