<?php

use Illuminate\Support\Str;

return [
    'trailing_slash' => true,
    'baseUrl' => 'http://jigsaw.test',
    'global_array' => [1, 2, 3],
    'global_variable' => 'some global variable',
    'number' => '98765',
    'perPage' => 4,
    'viewHintPaths' => [],
    'php' => '<?php',
    'nested_array' => [
        [
            'name' => 'first name',
            'position' => 'first',
            'value' => 1,
        ],
        [
            'name' => 'second name',
            'position' => 'second',
            'value' => 2,
        ],
        [
            'name' => 'third name',
            'position' => 'third',
            'value' => 3,
        ],
    ],
    'envVariable' => env('JIGSAW_TEST_VAR', false),
    'globalPreview' => function ($data, $characters = 100) {
        return substr(strip_tags($data->getContent() ?? ''), 0, $characters);
    },
    'helperFunction' => function ($data) {
        return 'hello global! #' . $data->number;
    },
    'selected' => function ($data, $section) {
        return strpos($data->getPath(), $section) > -1;
    },
    'collections' => [
        'remote_test' => [
            'extends' => '_layouts.remote',
            'items' => function () {
                $remote_post = json_decode(file_get_contents('https://jsonplaceholder.typicode.com/posts/1'));

                return [
                    [
                        'var' => 'test var 1',
                        'content' => '### The markdown content for the 1st item',
                    ],
                    [
                        'var' => 'test var 2',
                        'filename' => 'file_2',
                        'content' => '### The markdown content for the 2nd item',
                    ],
                    [
                        'var' => 'test var 3',
                    ],
                    [
                        'var' => $remote_post->title,
                        'content' => $remote_post->body,
                    ],
                    '## This item has no content key, just string content',
                ];
            },
        ],
        'collection_tests' => [
            'sum' => 99999,
        ],
        'sort_tests' => [
            'sort' => ['letter', '-number'],
        ],
        'invalid_path_characters_test' => [
            'path' => '{-title}',
        ],
        'posts' => [
            'helperFunction' => function ($data) {
                return 'hello from posts! #' . $data->number;
            },
            'author' => 'Default Author',
            'date_formatted' => function ($post) {
                [$year, $month, $day] = parseDate($post['date']);

                return sprintf('%s/%s/%s', $month, $day, $year);
            },
            'preview' => function ($post, $characters = 75) {
                return substr(strip_tags($post->getContent() ?? ''), 0, $characters);
            },
            'api' => function ($post) {
                return [
                    'slug' => Str::slug($post->title),
                    'title' => $post->title,
                    'author' => $post->author,
                    'date' => $post->date,
                    'content' => $post->getContent(),
                ];
            },
            'isSelected' => function ($post, $current_page) {
                return $post->getPath() == $current_page->getPath();
            },
        ],

        'people' => [
            'path' => [
                'web' => 'people/web/{date|Y-m-d}/{_filename}',
                'test' => 'people/test/{-filename}',
                'api' => 'people/api.test/{name}/{date|Y-m-d}/{-name}',
            ],
            'number_doubled' => function ($data) {
                return $data->number * 2;
            },
            'api' => function ($data) {
                return collect([
                    'name' => $data->name,
                    'number' => $data->number,
                    'role' => $data->role,
                    'content' => strip_tags($data->getContent() ?? ''),
                ])->toJson();
            },
        ],

        'dot-files-collection' => [
        ],
    ],
];

function parseDate($timestamp)
{
    $date = DateTime::createFromFormat('U', $timestamp);

    return [
        $date->format('Y'),
        $date->format('m'),
        $date->format('d'),
    ];
}
