---
extends: _layouts.post
title: My Third Post
author: Keith Damiani
date: 2016-03-01
number: 4
category: faq
---
@section('content')

<p>This file, named <strong>{{ $page->title }}</strong>, ends in <code>blade.php</code>, and therefore gets processed as by the Blade parser. It does not get parsed as a Markdown file.</p>

<p><em>NOTE: Blade-only collection items do not have a <code>getContent()</code> value at the moment. So functions that rely on it, such as the user-defined <code>preview()</code> function, will not return anything for now.</em></p>

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, placeat saepe, voluptatibus dignissimos expedita quae et sit quia ipsa error blanditiis delectus at consequatur doloremque ratione nesciunt commodi nihil temporibus.

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos tempora nostrum veritatis neque aliquam earum. Rerum accusamus repudiandae esse tempore doloribus necessitatibus natus ut ea, asperiores deserunt sequi cupiditate repellendus!

@endsection

@section('test')

<h1>Test Section</h1>

@endsection
