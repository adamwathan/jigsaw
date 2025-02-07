---
extends: _layouts.post
title: My Fourth Post
author: Matt Stauffer
date: 2016-04-01
number: 3
category: faq
some_variable: Blade
---

<div class="panel p-xs-4 m-xs-y-4">
    <h4>A Blade/Markdown hybrid.</h4>
</div>

This filename ends with `blade.md` and is processed by both the Blade parser (first) and then the Markdown parser (second).

So you can mix `{{ strtoupper($page->some_variable) }}` and `Markdown` directives in the same file.

> Note that in `blade.md` files, `section` can be omitted (as it can in any `.md` file).

You can also __include__ any type of file, which will get parsed based on its own file type:


- @include('include-test-markdown')


- @include('include-test-blade')


- @include('include-test-both')


- @include('include-test-text')



