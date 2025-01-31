---
title: A Regular Markdown Page
number: 1991
extends: _layouts/index
date: 1/16/2017
---

## Blade Markdown Page
#### Number: {{ $page->number }}
#### Global Variable: {{ $page->global_variable }}
#### Path: {{ $page->getPath() }}
#### Local Title: {{ $page->title }}
#### URL: {{ $page->getUrl() }}

#### Helper function: {{ $page->helperFunction(25) }}

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, placeat saepe, voluptatibus dignissimos expedita quae et sit quia ipsa error blanditiis delectus at consequatur doloremque ratione nesciunt commodi nihil temporibus.

{c:#f00}This is Red Text, demonstrating customizing the Markdown parser in `bootstrap.php`. {/c}

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos tempora nostrum veritatis neque aliquam earum. Rerum accusamus repudiandae esse tempore doloribus necessitatibus natus ut ea, asperiores deserunt sequi cupiditate repellendus!
