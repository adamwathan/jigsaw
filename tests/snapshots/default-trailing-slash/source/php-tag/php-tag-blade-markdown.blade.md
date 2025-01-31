---
title: Testing <?php tag
extends: _layouts/test-base
section: content
---

Title: {{ $page->title }}

```
<?php

public function store()
{
    $test = true;
}
```
