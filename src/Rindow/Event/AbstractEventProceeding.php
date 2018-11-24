<?php
namespace Rindow\Event;

use Iterator;
/*use Rindow\Container\ServiceLocator;*/
use Rindow\Event\Exception;

abstract class AbstractEventProceeding
{
    protected $event;
    protected $terminator;
    protected $iterator;
    protected $serviceLocator;
    protected $listeners = array();
    protected $currentListener;

    abstract protected function preListener($current,array $arguments);
    abstract protected function postListener($result,$current,array $arguments);
    abstract protected function exceptionListener($exception,$current,array $arguments);
    abstract public function preTerminator($terminator,array $arguments);
    abstract public function postTerminator($result,$terminator,array $arguments);
    abstract public function exceptionTerminator($exception,$terminator,array $arguments);

    public function __construct(
        EventInterface $event,
        $terminator,
        Iterator $iterator,
        /*ServiceLocator*/ $serviceLocator=null)
    {
        $this->event = $event;
        $this->terminator = $terminator;
        $this->iterator = $iterator;
        $this->serviceLocator = $serviceLocator;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function doNullTerminator($arguments)
    {
        if(is_array($arguments) && array_key_exists(0, $arguments))
            return $arguments[0];
        else
            return null;
    }

    public function resolvTerminator($terminator)
    {
        if(!is_callable($terminator)) {
            if(is_string($terminator)) {
                if(!$this->serviceLocator)
                    throw new Exception\DomainException('it need a service locator to instantiate terminator if you want to set null to terminate in the event "'.$this->event->getName().'".');
                $terminator = $this->serviceLocator->get($terminator);
            } elseif($terminator instanceof EventListner) {
                $terminator = $terminator->resolve($this->event,$this->serviceLocator);
            } else {
                $type = is_object($terminator) ? get_class($terminator) : gettype($terminator);
                throw new Exception\InvalidArgumentException('"'.$type.'" is invalid terminator callback on event "'.$this->event->getName().'"' );
            }
        }
        return $terminator;
    }

    public function proceed()
    {
        $arguments = func_get_args();
        if(!$this->iterator->valid()) {
            if($this->terminator==null) {
                return $this->doNullTerminator($arguments);
            }
            $this->terminator = $this->resolvTerminator($this->terminator);
            $this->startTerminator();
            try {
                $terminatorArgs = $this->preTerminator($this->terminator,$arguments);
                $result = call_user_func_array($this->terminator, $terminatorArgs);
                return $this->postTerminator($result,$this->terminator,$terminatorArgs);
            } catch(\Exception $e) {
                $e = $this->exceptionTerminator($e,$this->terminator,$terminatorArgs);
                $this->endTerminator();
                throw $e;
            }
            $this->endTerminator();
        }
        $listener = $this->iterator->current();
        $current = $listener->resolve($this->event,$this->serviceLocator);

        $this->iterator->next();
        array_push($this->listeners,$this->currentListener);
        $this->currentListener = $listener;
        try {
            $listenerArgs = $this->preListener($current,$arguments);
            // *** CAUTION ***
            // Saving calling nest depth patch
            // call_user_func_xxx　waste nest depth
            //
            if($this->isAopMode()&&$this->isClassAndMethod($current)) {
                $obj = $current[0];
                $method = $current[1];
                $result = $obj->$method($this);
            } elseif($this->isPsr7Mode()&&is_object($current)&&
                isset($arguments[0])&&isset($arguments[1])) {
                $result = $current->__invoke($arguments[0],$arguments[1],$this);
            } else {
                $result = call_user_func_array($current, $listenerArgs);
            }
            $result = $this->postListener($result,$current,$listenerArgs);
        } catch(\Exception $e) {
            $e = $this->exceptionListener($e,$current,$listenerArgs);
            $this->currentListener = array_pop($this->listeners);
            throw $e;
        }
        $this->currentListener = array_pop($this->listeners);
        return $result;
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

    protected function isAopMode()
    {
        return false;
    }

    protected function isPsr7Mode()
    {
        return false;
    }

    protected function startTerminator()
    {
    }

    protected function endTerminator()
    {
    }

    public function getListener()
    {
        return $this->currentListener;
    }
}
