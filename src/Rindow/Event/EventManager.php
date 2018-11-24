<?php
namespace Rindow\Event;

use Iterator;
/*use Rindow\Container\ServiceLocator;*/

class EventManager extends AbstractEventManager
{
    protected function doNotify($callback,$event,$listener)
    {
        return call_user_func($callback,$event,$listener);
    }

    protected function createProceeding(
        EventInterface $event,
        array $args,
        $terminator,
        Iterator $iterator,
        /*ServiceLocator*/ $serviceLocator=null)
    {
        $proceeding = new EventProceeding(
            $event,
            $terminator,
            $iterator,
            $serviceLocator);
        return array($proceeding, $args);
    }
/*
    protected function callTerminator($terminator, $event, array $args)
    {
        array_unshift($args, $event);
        return call_user_func_array($terminator, $args);
    }
*/
}