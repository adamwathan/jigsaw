---
title: Testing Escaping
extends: _layouts/index
section: content
number: 12345
---
@section('content')

@include('_layouts/verbatim-test')

<h3>{{ $page->getFilename() }}</h3>

Can we have php opening tags here?

This works: {{ $page->php }}
This works: {{ '<' . '?php' }}
This works: &lt;?php
This works: @include('_php')




<hr>
Start...
<hr>
&lt;?php

```php
<>?php
// Test comment...

public function store()
{
    $test = true;
}
```
<hr>
End.
<hr>
@endsection()
