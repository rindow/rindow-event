<?php
namespace Rindow\Event;

use Iterator;
use Rindow\Stdlib\PriorityQueue;
/*use Rindow\Container\ServiceLocator;*/
use Rindow\Event\Exception;

abstract class AbstractEventManager implements EventManagerInterface
{
    protected $serviceLocator;
	protected $queues=array();
    protected $queueClassName = 'Rindow\Stdlib\PriorityQueue';
    protected $priorityCapability = true;
    protected $ignored = false;

    abstract protected function doNotify($callback,$event,$listener);

    abstract protected function createProceeding(
        EventInterface $event,
        array $args,
        $terminator,
        Iterator $iterator,
        /*ServiceLocator*/ $serviceLocator=null);

    //abstract protected function callTerminator($terminator, $event, array $args);

    public function setQueueClassName($queueClassName,$priorityCapability)
    {
        $this->queueClassName = $queueClassName;
        $this->priorityCapability = $priorityCapability;
    }

    public function setServiceLocator(/*ServiceLocator*/ $serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function attach($eventName, $callback, $priority = 1)
    {
        if($callback instanceof EventListener) {
            $listener = $callback;
            $callback = $listener->getCallback();
            if($callback!=null && !is_callable($callback) && !is_string($callback)) {
                throw new Exception\InvalidArgumentException('invalid callback in event "'.$eventName.'".');
            }
        } else {
            if(!is_callable($callback) && !is_string($callback)) {
                throw new Exception\InvalidArgumentException('invalid callback in event "'.$eventName.'".');
            }
            $listener = new EventListener($callback);
        }
        $queueClass = $this->queueClassName;
    	if(!isset($this->queues[$eventName]))
            $this->queues[$eventName] = new $queueClass();
        if($this->priorityCapability)
            $this->queues[$eventName]->insert($listener,$priority);
        else
            $this->queues[$eventName][] = $listener;
        return $listener;
    }

    protected function toEvent($event)
    {
        if(is_string($event)||is_array($event)) {
            $eventNames = $event;
            $event = new Event();
            $event->setName($eventNames);
        } else if($event instanceof EventInterface) {
            ;
        } else {
            throw new Exception\InvalidArgumentException('invalid event type.');
        }
        return $event;
    }

    protected function select($event)
    {
        $event = $this->toEvent($event);
        $eventNames = $event->getNames();
        if(!is_array($eventNames)) {
            $eventNames = array($eventNames);
        }
        $eventQueue = new PriorityQueue();
        $found = null;
        foreach ($eventNames as $eventName) {
            if(!is_string($eventName))
                throw new Exception\InvalidArgumentException('invalid event name.');
            if(!isset($this->queues[$eventName]))
                continue;
            $eventQueue->merge($this->queues[$eventName]);
            $found = $event;
        }
        return array($found,$eventQueue);
    }

    public function notify(
        $event,
        array $parameters = null,
        $target = null,
        $previousResult=null)
    {
        list($event,$eventQueue) = $this->select($event);
        if($event==null)
            return $previousResult;
        if($parameters!==null) // ** CAUTION ** MUST compare to use "!=="
            $event->setParameters($parameters);
        if($target)
            $event->setTarget($target);
        if($previousResult)
            $event->setPreviousResult($previousResult);
        $event->setBreak(false);
        foreach ($eventQueue as $listener) {
            $callback = $listener->resolve($event,$this->serviceLocator);
            $previousResult = $this->doNotify($callback,$event,$listener);
            $event->setPreviousResult($previousResult);
            if($event->getBreak())
                break;
        }
        return $previousResult;
    }

    public function prepareCall($event)
    {
        list($foundEvent,$eventQueue) = $this->select($event);
        if($foundEvent==null)
            return null;
        return $eventQueue;
    }

    public function call(
        $event,
        array $args=null,
        $terminator=null,
        $eventQueue=null)
    {
        if($args==null)
            $args = array();
        if($eventQueue==null) {
            list($foundEvent,$eventQueue) = $this->select($event);
            $event = $this->toEvent($event);
        } else {
            $event = $this->toEvent($event);
            $foundEvent = $event;
        }
        list($proceeding,$arguments) = $this->createProceeding(
            $event,
            $args,
            $terminator,
            $eventQueue->getIterator(),
            $this->serviceLocator);

        if($foundEvent==null) {
            if($terminator==null) {
                return $proceeding->doNullTerminator($arguments);
            }
            $terminator = $proceeding->resolvTerminator($terminator);
            try {
                $terminatorArgs = $proceeding->preTerminator($terminator,$arguments);
                $result = call_user_func_array($terminator, $terminatorArgs);
                return $proceeding->postTerminator($result,$terminator,$terminatorArgs);
            } catch(\Exception $e) {
                $e = $proceeding->exceptionTerminator($e,$terminator,$terminatorArgs);
                throw $e;
            }
        }

        $this->startProceeding();
        try {
            // *** CAUTION ***
            // Saving calling nest depth patch
            // call_user_func_xxx　waste nest depth
            //
            if($this->isAopMode()) {
                $result = $proceeding->proceed();
            } elseif($this->isPsr7Mode()&&isset($arguments[0])&&isset($arguments[1])) {
                $result = $proceeding->proceed($arguments[0],$arguments[1]);
            } else {
                $result = call_user_func_array(array($proceeding,'proceed'), $arguments);
            }
        } catch(\Exception $e) {
            $this->endProceeding();
            throw $e;
        }
        $this->endProceeding();
        return $result;
    }

    protected function isAopMode()
    {
        return false;
    }

    protected function isClassAndMethod($callable)
    {
        if(is_array($callable)&&count($callable)==2&&
            isset($callable[0])&&is_object($callable[0])&&
            isset($callable[1])&&is_string($callable[1])) {
            return true;
        } else {
            return false;
        }
    }

    protected function startProceeding()
    {
    }

    protected function endProceeding()
    {
    }

    protected function isPsr7Mode()
    {
        return false;
    }

/*
    protected function postProceeding(
        $result,
        EventInterface $event,
        array $args,
        $terminator,
        $proceeding,
        array $proceedingArgs)
    {
        return $result;
    }

    protected function exceptionProceeding(
        $exception,
        EventInterface $event,
        array $args,
        $terminator,
        $proceeding,
        array $proceedingArgs)
    {
        return $exception;
    }
*/
    public function fetch($eventName)
    {
        if(!isset($this->queues[$eventName]))
            return null;
        return $this->queues[$eventName];
    }

    public function getEventNames()
    {
        return array_keys($this->queues);
    }
}