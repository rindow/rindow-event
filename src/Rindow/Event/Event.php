<?php
namespace Rindow\Event;

class Event implements EventInterface
{
    protected $name;
    protected $parameters;
    protected $target;
    protected $previousResult;
    protected $breakStatus;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        if(is_array($this->name)) {
            reset($this->name);
            return current($this->name);
        }
        return $this->name;
    }
    
    public function getNames()
    {
        return $this->name;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setBreak($status)
    {
        $this->breakStatus = $status;
        return $this;
    }

    public function getBreak()
    {
        return $this->breakStatus;
    }

    public function getPreviousResult()
    {
        return $this->previousResult;
    }

    public function setPreviousResult($previousResult)
    {
        $this->previousResult = $previousResult;
        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name,$default=null)
    {
    	if(is_array($this->parameters) && array_key_exists($name,$this->parameters))
            return $this->parameters[$name];
        return $default;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setParameter($name,$value)
    {
        $this->parameters[$name] = $value;
        return $this;
    }
}