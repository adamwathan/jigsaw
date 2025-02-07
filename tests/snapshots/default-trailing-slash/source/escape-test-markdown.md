---
title: Testing Escaping
extends: _layouts/index
section: content
---
Can we have php opening tags here?
<hr>
Start...
<hr>
```php
<?php
// Test comment...

public function store()
{
    $test = true;
    {{ a-blade-echo-that-should-remain-in-markdown }}
    {!! a-blade-danger-echo-that-should-remain-in-markdown !!}
}
```
<hr>
End.
<hr>
