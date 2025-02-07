<div class="panel m-xs-t-6">
    <div class="panel-heading">
        <h4 class="text-sm wt-light text-uppercase text-brand">Page meta</h4>
    </div>

    <div class="panel-body">
        <div class="p-xs-b-4 border-b">
            <p class="text-xs text-dark-soft text-uppercase">Filename:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->getFilename() }}</p>
        </div>

        <div class="p-xs-y-4 border-b">
            <p class="text-xs text-dark-soft text-uppercase">Extension:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->getExtension() }}</p>
        </div>

        <div class="p-xs-y-4 border-b">
            <p class="text-xs text-dark-soft text-uppercase">Path:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->getPath() }}</p>
        </div>

        <div class="p-xs-y-4 border-b">
            <p class="text-xs text-dark-soft text-uppercase">Base URL:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->baseUrl }}</p>
        </div>

        <div class="p-xs-y-4 border-b">
            <p class="text-xs text-dark-soft text-uppercase">URL:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->getUrl() }}</p>
        </div>

        <div class="p-xs-t-4">
            <p class="text-xs text-dark-soft text-uppercase">Global Variable:</p>
            <p class="p-xs-l-2 text-sm">{{ $page->global_variable }}</p>
        </div>
    </div>
</div>
