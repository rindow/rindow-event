<?php
namespace RindowTest\Event\EventTest;

use PHPUnit\Framework\TestCase;
// Test Target Classes
use Rindow\Event\Event;

class EventTest extends TestCase
{
    public function setUp()
    {
    }

    public function testEvent()
    {
        $event = new Event();
        $event->setName('test');
        $this->assertEquals('test',$event->getName());
        $event->setTarget('target');
        $this->assertEquals('target',$event->getTarget());
        $event->setBreak('break');
        $this->assertEquals('break',$event->getBreak());
        $event->setPreviousResult('result');
        $this->assertEquals('result',$event->getPreviousResult());
        $event->setParameters(array('ag1'=>'val1'));
        $this->assertEquals(array('ag1'=>'val1'),$event->getParameters());
        $this->assertEquals('val1',$event->getParameter('ag1'));
        $this->assertEquals('default',$event->getParameter('agx','default'));
        $event->setParameter('ag2','val2');
        $this->assertEquals(array('ag1'=>'val1','ag2'=>'val2'),$event->getParameters());

        $event = new Event();
        $this->assertEquals('default',$event->getParameter('agx','default'));
        $event->setParameter('ag2',null);
        $this->assertEquals(null,$event->getParameter('ag2','default'));
    }
}
