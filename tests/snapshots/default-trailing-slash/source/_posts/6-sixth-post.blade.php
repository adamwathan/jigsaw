---
extends: _layouts.post
title: My Sixth Post
date: 2016-06-01
number: 1
category: faq
---
@section('content')


<div class="panel p-xs-4 m-xs-y-4">
    <h4>Test of YAML Frontmatter in a Blade post:</h4>
    Title: <em>{{ $page->title }}</em><br>
    Author: <em>{{ $page->author }}</em><br>
    Category: <em>{{ $page->category }}</em><br>

    Number: <em>{{ $page->number }}</em>
</div>

<h3>Collection name: {{ $page->getCollection() }} </h3>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, placeat saepe, voluptatibus dignissimos expedita quae et sit quia ipsa error blanditiis delectus at consequatur doloremque ratione nesciunt commodi nihil temporibus.</p>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos tempora nostrum veritatis neque aliquam earum. Rerum accusamus repudiandae esse tempore doloribus necessitatibus natus ut ea, asperiores deserunt sequi cupiditate repellendus!</p>

@endsection
