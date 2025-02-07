<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <link rel="stylesheet" href="{{ $page->baseUrl }}/css/main.css">
    </head>
    <body class="border-t-3 border-primary full-height">

        <nav class="navbar navbar-brand">
            <div class="container">
                <div class="navbar-content">
                    <div>
                        <a class="link-plain text-xxl flex-y-center" href="{{ $page->baseUrl }}">
                            <strong>Jigsaw Collections Demo</strong>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container m-xs-b-6">
            <div class="row">

                <div class="col-xs-4">
                    @include('_layouts.sidebar.sections')
                    @include('_layouts.sidebar.meta')
                    @yield('sidebar')
                </div>

                <div class="col-xs-8 demo-page">
                    @yield('body')
                </div>
            </div>
        </div>
    </body>
</html>
<!DOCTYPE html>
