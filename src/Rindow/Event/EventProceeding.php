<?php
namespace Rindow\Event;

class EventProceeding extends AbstractEventProceeding
{
    protected function preListener($current,array $arguments)
    {
        array_unshift($arguments, $this);
        return $arguments;
    }

    protected function postListener($result,$current,array $arguments)
    {
    	return $result;
    }

    protected function exceptionListener($exception,$current,array $arguments)
    {
        return $exception;
    }

    public function preTerminator($terminator,array $arguments)
    {
        array_unshift($arguments, $this->getEvent());
        return $arguments;
    }

    public function postTerminator($result,$terminator,array $arguments)
    {
    	return $result;
    }

    public function exceptionTerminator($exception,$terminator,array $arguments)
    {
        return $exception;
    }
}