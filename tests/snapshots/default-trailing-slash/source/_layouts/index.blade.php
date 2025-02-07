@extends('_layouts.master')

@section('body')
    <h2>{{ $page->title }}</h2>
    @yield('content')
@endsection
