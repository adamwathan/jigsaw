---
extends: _layouts/index
title: "Features"
section: content
---

### Enhanced permalink support
- Permalinks for collection items can be configured with the optional `path` key in `collections.php`
- If `path` is not specified, permalinks will default to slugified filename
- Global `slugify()` helper added for easy use in permalink or helper functions
- Shorthand for easily specifying common permalink structures:
  - `path` key can be a string rather than a closure; if it's a string, any bracketed parameters like `{filename}` will be replaced with values
  - prepending a separator to a parameter name will slugify that parameter, using that separator (e.g. `{+filename}` will yield `the+slugified+filename`)
  - any dates from YAML front matter can be formatted using PHP date formatting codes, by following the parameter name with a pipe: `{date|Y/m/d}`, for example, yields `2016/08/29`.
  - if no parameters are included, the slugified filename will be appended by default. This allows users to simply specify a collection subdirectory: `'path' => 'posts'` will yield item URLs like `/posts/the-slugified-filename`.

  - __Examples of__ `path` __shorthand syntax:__

    - `'people'` yields `/people/the-slugified-filename`
    - `'people/{+filename}'` yields `./people/the+slugified+filename`
    - `'{collection}/{date|Y}/{title}'` yields `./people/2016/The Item Title`
    - `'{collection}/{date|Y/m/d}/{+title}'` yields `./people/2016/08/29/the+item+title`

---

### Default frontmatter variables
- Default frontmatter variables at the collection level can be specified with the `variables` key in `collections.php`:
```
'posts' => [
    'variables' => [
        'author' => 'Editorial Staff'
    ],
...
```

---

### Sorting collections
- Collections can be sorted by one or more criteria specified in `collections.php`.
- `sort` can contain a single variable name, or an array of multiple variables for a hierarchical sort.
- Sort order defaults to ascending; variable names can optionally be prepended with `+`.
- Variable names can be prepended with `-` for a descending sort order.
```
'posts' => [
    'sort' => '-date',
...
```
or
```
'posts' => [
    'sort' => ['-date', '+author'],
...
```

---

### Collection items contain a reference to other collection items
- `$items` can reference other collection items using `getNext()`, `getPrevious()`, `getFirst()`, `getLast()`
- Next/previous collection items are based on collection's default sort order
- e.g. `$item->getNext()->title` or `$item->getPrevious()->path`

---

### Collections can exist without templates
- The `extends` directive can be omitted from the YAML front matter of a collection's items, allowing for collections that aren't tied to a particular template. See the "People" collection for an example.

---

### Access Jigsaw collections as Illuminate collections
- For example, `@foreach ($people->sortBy('age') as $person)` or `$products->sum('price')`
- Can even use helper functions defined in `config.php` or `collections.php`:
```
@foreach ($products->sortByDesc(function ($data) { return $data->priceWithTax(); })
```
- Content of a collection item can be referenced with `getContent()`.

---

### Additional pagination variables
- `$pagination->first`, `last`, `currentPage`, `totalPages`
- `$pagination->pages` can be iterated over to build numeric page links (`1` | `2` | `3`), or individual pages can be referenced as `$pagination->pages[1]`

---

### Helper functions
- Any helper functions defined in `collections.php` under the `helpers => []` key are available within Blade templates for a collection item; previously, they were only available in an index template when iterating over a collection.
- Helper functions can also be defined globally in `config.php` and referenced in any Blade template as `$functionName($parameter)`

---

### Metadata
- available in Blade templates for all pages (including collection items and regular pages)
- `filename` displays current page's filename (without extension)
- `extension` displays current page's file extension (e.g. `md`, `blade.php`, etc.)
- `path` is path to current page, relative to site root
- `url` concatenates the site base url (if specified in `config.php`) with `path` for fully-qualified url to current page
- for collection items, `collection` displays the name of the collection an item belongs to

---

### Enhanced Blade and Markdown support
- Collection items can now be Blade files, in addition to Markdown
- Blade files (whether they are items in a collection, or not) can include YAML frontmatter variables
- Markdown files can use Blade syntax in them—for displaying data, control structures, etc. Files with a `.blade.md` extension will be processed first by Blade, before the markdown is parsed.
- Blade templates can `@include` markdown files as partials (which will be parsed). Addresses feature request https://github.com/tighten/jigsaw/issues/62

---

### Blade support for other file types
- Like the `.blade.md` support described above, other non-HTML, text-type files can be processed with Blade first, including `.blade.js`, `.blade.json`, `.blade.xml`, `.blade.rss`, `.blade.txt`, and `.blade.text`. After the file is first processed by Blade, the resulting file will maintain its filetype extension in the URL (e.g. `some-file.blade.xml` will become the URL `/some-file.xml`).
- Addresses feature request https://github.com/tighten/jigsaw/issues/56

---

### Support for multiple parent templates
- Collection items can extend multiple parent templates, by specifying them in the `extends` parameter in the YAML front matter. This creates one URL for each template, allowing, for example, a collection item to have `/web/item` and a `/api/item` endpoints, or `/summary` and `/detail` views.
- In `collections.php`, permalink structures can be specified uniquely for each template type:

```
    'path' => [
        'web' => 'people/{date|Y-m-d}/{+filename}',
        'api' => 'people/api/{date|Y-m-d}/{+filename}'
    ]
```

---

### Updates to variable referencing
- Globally-available variables and helper functions that are defined in `config.php` are accessed with the `$config->` namespace, e.g. `$config->some_variable` or `$config->someFunction()`
- Collections defined in `collections.php` are referenced by their name, with no prefix. A collection named `posts` would be referenced as `$posts`, e.g. `$posts->first()->path`
- Collection items and their variables/functions are referenced by `$item`, e.g. `$item->author` or `$item->excerpt()`
- For better readability in templates, collection items can also be referenced automatically by the singular version of the collection name, _if_ the collection name is plural. So the items of a collection of `people` can be referenced as `$person->first_name`. `$person->first_name` and `$item->first_name` will return the same thing. (If a collection name's plural and singular are the same, this shorthand won't be available; so a collection of `$sheep` is out of luck.)

---

### 5.3 Update
- Illuminate dependencies have been updated to version 5.3
- The code for allowing a custom `bootstrap.php` file, providing the ability to customize bindings (for extending the markdown parser, for instance) has been updated (from https://github.com/tighten/jigsaw/pull/68)
