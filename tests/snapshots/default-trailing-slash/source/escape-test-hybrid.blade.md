---
title: Testing Escaping
extends: _layouts/index
section: content
---

<h3>{{ $page->getFilename() }}</h3>

Can we have php opening tags here?

@verbatim
    &lt;?php
    @if('something')
    @endif
@endverbatim

```
<?php
```

<hr>
Start...
<hr>

This works: @include('_php') (where _php is a .md file)

```php
ok

Only an issue with blade.md files:

This does not work: {{ $page->php }}
This works: <?php{{ '' }}
This works: {!! $page->php !!}
This works: {!! '<' . '?php' !!}
This works: <{{ '?php' }}
This works: <?php

// Test comment...

@verbatim
stuff in here is rendered verbatim
@endverbatim

public function store()
{
    $test = true;
}
```
<hr>
End.
<hr>
