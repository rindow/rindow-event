<?php
namespace RindowTest\Event\EventListenerTest;

use PHPUnit\Framework\TestCase;
// Test Target Classes
use Rindow\Event\EventListener;

class EventListenerTest extends TestCase
{
    public function setUp()
    {
    }

    public function testEventListener()
    {
        $event = new EventListener('callback','componentname','methodname');
        $this->assertEquals('callback',$event->getCallBack());
        $this->assertEquals('componentname',$event->getComponentName());
        $this->assertEquals('methodname',$event->getMethodName());

        $event = new EventListener();
        $this->assertEquals(null,$event->getCallBack());
        $this->assertEquals(null,$event->getComponentName());
        $this->assertEquals(null,$event->getMethodName());
        $event->setCallBack('foofunc');
        $this->assertEquals('foofunc',$event->getCallBack());
    }
}
