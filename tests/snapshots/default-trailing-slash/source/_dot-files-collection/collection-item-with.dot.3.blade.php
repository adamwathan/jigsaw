---
title: Collection item with dot in filename
---

@extends('_layouts/test-base')

@section('content')
    <h3>This file contains a dot in the filename</h3>
    <p>{{ $page->getFilename() }}</p>
    <p>{{ $page->getPath() }}</p>
@endsection
