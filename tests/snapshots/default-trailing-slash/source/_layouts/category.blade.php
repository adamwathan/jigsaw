@extends('_layouts.master')

@section('body')

<h2>Categories</h2>

<div class="border-b">
    <div class="row">
        <div class="col-xs-6">
            <h2 class="text-brand"><em>{{ $page->getFilename() }}</em></h2>
        </div>
        <div class="col-xs-6 text-right">
            <h2>

                @foreach ($posts->pluck('category')->unique() as $category)
                <a class="btn btn-primary-outline btn-sm m-xs-l-2 text-uppercase" href="{{ $page->baseUrl }}/categories/{{ $category }}">{{ $category }}</a>
                @endforeach

            </h2>
        </div>
    </div>
</div>

<blockquote class="m-xs-t-4">Demonstrates using collection methods to build pages dynamically</blockquote>

@foreach ($posts->where('category', $page->getFilename()) as $post)
<div class="row">
    <div class="col-xs-12">
        <h3><a href="{{ $post->getUrl() }}">{{ $post['title'] }}</a></h3>
        <p class="text-sm">by {{ $post->author }} · {{ $post->date_formatted() }} · Number {{ $post->number }}</p>
        <p class="p-xs-b-6 border-b">{!! $post->preview(180) !!}...</p>
    </div>
</div>
@endforeach

@endsection
