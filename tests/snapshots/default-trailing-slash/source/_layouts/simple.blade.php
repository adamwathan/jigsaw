@extends('_layouts.master')

@section('body')
    <h1>Simple Test</h1>
    <h2>{{ $page->title }}</h2>

    @yield('content')
@endsection
