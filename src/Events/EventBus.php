<?php

namespace TightenCo\Jigsaw\Events;

use Illuminate\Support\Arr;
use TightenCo\Jigsaw\Jigsaw;

/**
 * @method void beforeBuild(\callable|class-string|array<int, class-string> $listener)
 * @method void afterCollections(\callable|class-string|array<int, class-string> $listener)
 * @method void afterBuild(\callable|class-string|array<int, class-string> $listener)
 */
class EventBus
{
    /** @var \Illuminate\Support\Collection */
    public $beforeBuild;

    /** @var \Illuminate\Support\Collection */
    public $afterCollections;

    /** @var \Illuminate\Support\Collection */
    public $afterBuild;

    public function __construct()
    {
        $this->beforeBuild = collect();
        $this->afterCollections = collect();
        $this->afterBuild = collect();
    }

    public function __call($event, $arguments)
    {
        if (isset($this->{$event})) {
            $this->{$event} = $this->{$event}->merge(Arr::wrap($arguments[0]));
        }
    }

    public function fire($event, Jigsaw $jigsaw)
    {
        $this->{$event}->each(function ($task) use ($jigsaw) {
            if (is_callable($task)) {
                $task($jigsaw);
            } else {
                (new $task)->handle($jigsaw);
            }
        });
    }
}
