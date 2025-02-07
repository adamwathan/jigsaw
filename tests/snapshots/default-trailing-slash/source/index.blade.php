---
pagination:
  collection: posts
  perPage: 2
testvar: Successful!
---
@extends('_layouts.master')

@section('body')
<h2>Pagination</h2>

<h3>Testing default perPage: {{ $page->perPage }}</h3>

<div class="p-xs-y-3 border-b">
    <div class="row">
        <div class="col-xs-6">
            <h4 class="text-uppercase text-dark-soft wt-light">
                Current page: {{ $pagination->currentPage }}
            </h4>
            <h4 class="text-uppercase text-dark-soft wt-light">
                Total pages: {{ $pagination->totalPages }}
            </h4>
            <h4 class="text-uppercase text-dark-soft">
                Test of a local variable: {{ $page->testvar }}
            </h4>
        </div>
        <div class="col-xs-6 text-right">
            @if ($previous = $pagination->previous)
                <a href="{{ $page->baseUrl }}{{ $pagination->first }}">&lt;&lt;</a>
                <a href="{{ $page->baseUrl }}{{ $previous }}">&lt;</a>
            @else
                &lt;&lt; &lt;
            @endif

            @foreach ($pagination->pages as $number => $page_value)
                <a href="{{ $page->baseUrl }}{{ $page_value }}" class="pagination__number {{ $pagination->currentPage == $number ? 'selected' : '' }}">{{ $number }}</a>
            @endforeach

            @if ($next = $pagination->next)
                <a href="{{ $page->baseUrl }}{{ $next }}">&gt;</a>
                <a href="{{ $page->baseUrl }}{{ $pagination->last }}">&gt;&gt;</a>
            @else
                &gt; &gt;&gt;
            @endif
        </div>
    </div>
</div>

@foreach ($pagination->items as $post)
<div class="row">
    <div class="col-xs-12">
        <h3><a href="{{ $post->getUrl() }}">{{ $post->title }}</a></h3>
        <p class="text-sm">by {{ $post->author }} · {{ $post->date_formatted() }} · Number {{ $post->number }}</p>
        <div class="p-xs-b-6 border-b">{!! $post->getContent() !!}</div>
    </div>
</div>
@endforeach

@endsection
