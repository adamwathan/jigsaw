<nav class="nav-list">
    <a class="nav-list-item {{ $page->selected('posts') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/posts">
        <icon></icon>Posts
    </a>

    <a class="nav-list-item {{ $page->selected('pagination') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/pagination">
        <icon></icon>Pagination
    </a>

    <a class="nav-list-item {{ $page->selected('categories') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/categories/news">
        <icon></icon>Categories
    </a>

    <a class="nav-list-item {{ $page->selected('people') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/people">
        <icon></icon>People
    </a>

    <a class="nav-list-item {{ $page->selected('variables') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/variables">
        <icon></icon>Variables
    </a>

    <a class="nav-list-item {{ $page->selected('json') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/json-test.json">
        <icon></icon>JSON
    </a>

    <a class="nav-list-item {{ $page->selected('json') ? 'selected' : '' }}" href="{{ $page->baseUrl }}/text-test.txt">
        <icon></icon>Text
    </a>
</nav>
