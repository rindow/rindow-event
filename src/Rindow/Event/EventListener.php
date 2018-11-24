<?php
namespace Rindow\Event;

class EventListener
{
    protected $callback;
    protected $componentName;
    protected $methodName;

    public function __construct($callback=null,$componentName=null,$methodName=null)
    {
        $this->callback = $callback;
        $this->componentName = $componentName;
        $this->methodName = $methodName;
    }

    public function getCallBack()
    {
        return $this->callback;
    }

    public function setCallBack($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function resolve($event,$serviceLocator=null)
    {
        $callback = $this->getCallback();
        if($callback==null) {
            if($serviceLocator==null)
                throw new Exception\DomainException('it need a service locator to instantiate callback if you want to set null to callback in the event "'.$event->getName().'".');
            $callback = array(
                $serviceLocator->get($this->getComponentName()),
                $this->getMethodName(),
            );
            if(!is_callable($callback))
                throw new Exception\DomainException('invalid listener.: '.$this->getComponentName().'::'.$this->getMethodName());
            $this->setCallback($callback);
        } else {
            if(!is_callable($callback)) {
                if($serviceLocator==null)
                    throw new Exception\DomainException('it need a service locator to instantiate callback if you want to set null to callback in the event "'.$event->getName().'".');
                $instance = $serviceLocator->get($callback);
                if(!is_callable($instance))
                    throw new Exception\DomainException('invalid listener.: '.$callback);
                $callback = $instance;
                unset($instance);
                $this->setCallback($callback);
            }
        }
        return $callback;
    }
}