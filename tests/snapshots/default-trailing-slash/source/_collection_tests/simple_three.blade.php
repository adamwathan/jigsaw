---
title: Test Three
author: Keith Damiani
date: 2016-01-03
number: 333
category: faq
---
@extends('_layouts.simple')

@section('content')
<h2>Test for components and slots</h2>

@component('_components.alert')
    @slot('title')
        Title test
    @endslot

    <strong>Whoops!</strong> Something went wrong!
@endcomponent

@endsection
