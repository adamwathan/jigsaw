@extends('_layouts.master')

@section('body')
<h2>Multisort Test</h2>

@foreach ($sort_tests as $item)
    <p>{{ $item->number }}-{{ $item->letter }}</p>
@endforeach

@endsection
