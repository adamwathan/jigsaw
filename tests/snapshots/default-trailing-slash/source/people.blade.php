@extends('_layouts.master')

@section('body')
<h2>People</h2>

<div class="panel p-xs-4 m-xs-y-4">
    <h4>Demonstrates:</h4>
    <ul>
        <li>Using collections without templates</li>
        <li>Defining multiple URL paths ('web' and 'api' in this example) in <code>collections.php</code></li>
        <li>Building a simple API that returns JSON</li>
    </ul>
</div>

<div class="row">
    <div class="col-xs-12">

        @foreach ($people as $person)
        <div class="p-xs-y-4 border-b">
            <h3>
                {{ $person->number }}

                @if ($person->getPath())
                    <a href="{{ $person->getUrl('web') }}">{{ $person->name }}</a>
                @else
                    {{ $person->name }}
                @endif
            </h3>

            <p>Filename (meta): {{ $person->getFilename() }}</p>

            @if ($person->getPath())
            <p>Path (WEB): <a href="{{ $person->getUrl('web') }}">{{ $person->getPath('web') }}</a></p>
            <p>Path (API): <a href="{{ $person->getUrl('api') }}">{{ $person->getPath('api') }}</a></p>
            <p>Path (Test): <a href="{{ $person->getUrl('test') }}">{{ $person->getPath('test') }}</a></p>
            @endif

            <p>Role (frontmatter): {{ collect($person->role)->implode(', ') }}</p>
            <p>Using global helper function: {{ $person->globalPreview() }}</p>
        </div>
        @endforeach

    </div>
</div>

<div class="row p-xs-y-8">
    <div class="col-xs-6">
        <blockquote>Can reference as an Illuminate Collection, e.g. for sorting:</blockquote>

        @foreach ($people->sortBy('number') as $person)
            <h3>{{ $person->name }} ({{ $person->number }})</h3>
        @endforeach

    </div>
    <div class="col-xs-6">
        <blockquote>Can reference collection-specific helper functions when sorting:</blockquote>

        @foreach ($people->sortByDesc(function($person) { return $person->number_doubled(); }) as $person)
            <h3>{{ $person->name }} ({{ $person->number_doubled() }})</h3>
        @endforeach

    </div>
</div>
@endsection
