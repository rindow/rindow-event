<?php
namespace RindowTest\Event\EventCallTest;

use PHPUnit\Framework\TestCase;
use Rindow\Container\Container;
use Rindow\Event\EventListener;
use Rindow\Event\Event;

// Test Target Classes
use Rindow\Event\EventManager;
use Rindow\Event\EventProceeding;

class Logger
{
    protected $log;

    public function log($message)
    {
        $this->log[] = $message;
    }
    public function getLog()
    {
        return $this->log;
    }
    public function reset()
    {
        $this->log=null;
    }
}

class Ev1
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(EventProceeding $invocation,$arg1,$arg2)
    {
        if($arg1!=='In'||$arg2!=='arg2')
            throw new \Exception("Error");
        $this->logger->log('ev1 front:'.$arg1);
        $result = $invocation->proceed($arg1,$arg2);
        $this->logger->log('ev1 back:'.$result);
        return $result;
    }
}

class Ev2
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(EventProceeding $invocation,$arg1,$arg2)
    {
        if($arg1!=='In'||$arg2!=='arg2')
            throw new \Exception("Error");
        $this->logger->log('ev2 front:'.$arg1);
        $result = $invocation->proceed($arg1,$arg2);
        $this->logger->log('ev2 back:'.$result);
        return $result;
    }
}

class Ev1Callable
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(EventProceeding $invocation,$arg1,$arg2)
    {
        if($arg1!=='In'||$arg2!=='arg2')
            throw new \Exception("Error");
        $this->logger->log('ev1callable front:'.$arg1);
        $result = $invocation->proceed($arg1,$arg2);
        $this->logger->log('ev1callable back:'.$result);
        return $result;
    }
}

class Ev2Callable
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(EventProceeding $invocation,$arg1,$arg2)
    {
        if($arg1!=='In'||$arg2!=='arg2')
            throw new \Exception("Error");
        $this->logger->log('ev2callable front:'.$arg1);
        $result = $invocation->proceed($arg1,$arg2);
        $this->logger->log('ev2callable back:'.$result);
        return $result;
    }
}

class Test extends TestCase
{
    public function setUp()
    {
    }

    public function testEventManagerNormal()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            if($invocation->getEvent()->getParameter('param1')!=='value1')
                throw new \Exception("Error");
            if($invocation->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            if($arg1!=='arg1'||$arg2!=='arg2')
                throw new \Exception("Error");
            $test->log[] = 'ev1 front:'.$invocation->getEvent()->getParameter(0);
            $result = $invocation->proceed('arg10','arg20');
            $test->log[] = 'ev1 back:'.$result;
            return $result;
        });
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            if($invocation->getEvent()->getParameter('param1')!=='value1')
                throw new \Exception("Error");
            if($invocation->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            if($arg1!=='arg10'||$arg2!=='arg20')
                throw new \Exception("Error");
            $test->log[] = 'ev2 front:'.$invocation->getEvent()->getParameter(0);
            $result = $invocation->proceed('arg100','arg200');
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $terminator = function ($event,$arg1,$arg2) use ($test) {
            $test->log[] = 'orig:'.$event->getParameter(0);
            if($arg1!=='arg100'||$arg2!=='arg200')
                throw new \Exception("Error");
            return 'Out';
        };

        $parameters = array(0=>'In','param1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $ev = new Event();
        $ev->setName('ev')
            ->setParameters($parameters)
            ->setTarget($target);
        $args = array('arg1','arg2');

        $this->assertEquals('Out',$events->call($ev,$args,$terminator));
        $this->assertEquals('ev1 front:In',$test->log[0]);
        $this->assertEquals('ev2 front:In',$test->log[1]);
        $this->assertEquals('orig:In',$test->log[2]);
        $this->assertEquals('ev2 back:Out',$test->log[3]);
        $this->assertEquals('ev1 back:Out',$test->log[4]);
    }

    public function testEventManagerNoEvent()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();

        $terminator = function ($event,$arg1,$arg2) use ($test) {
            $test->log[] = 'orig:'.$event->getParameter(0);
            if($arg1!=='arg1'||$arg2!=='arg2')
                throw new \Exception("Error");
            return 'Out';
        };

        $parameters = array(0=>'In','param1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $ev = new Event();
        $ev->setName('ev')
            ->setParameters($parameters)
            ->setTarget($target);
        $args = array('arg1','arg2');

        $this->assertEquals('Out',$events->call($ev,$args,$terminator));
        $this->assertEquals('orig:In',$test->log[0]);
    }

    public function testEventManagerNonTerminator()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            if($invocation->getEvent()->getParameter('param1')!=='value1')
                throw new \Exception("Error");
            if($invocation->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            if($arg1!=='arg1'||$arg2!=='arg2')
                throw new \Exception("Error");
            $test->log[] = 'ev1 front:'.$invocation->getEvent()->getParameter(0);
            $result = $invocation->proceed('arg10','arg20');
            $test->log[] = 'ev1 back:'.$result;
            return $result;
        });
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            if($invocation->getEvent()->getParameter('param1')!=='value1')
                throw new \Exception("Error");
            if($invocation->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            if($arg1!=='arg10'||$arg2!=='arg20')
                throw new \Exception("Error");
            $test->log[] = 'ev2 front:'.$invocation->getEvent()->getParameter(0);
            $result = $invocation->proceed('arg100','arg200');
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $parameters = array(0=>'In','param1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $ev = new Event();
        $ev->setName('ev')
            ->setParameters($parameters)
            ->setTarget($target);
        $args = array('arg1','arg2');

        $this->assertEquals('arg100',$events->call($ev,$args,null));
        $result = array(
            'ev1 front:In',
            'ev2 front:In',
            'ev2 back:arg100',
            'ev1 back:arg100',
        );
        $this->assertEquals($result,$test->log);
    }

    public function testEventManagerNonEventNonTerminator()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();

        $parameters = array(0=>'In','param1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $ev = new Event();
        $ev->setName('ev')
            ->setParameters($parameters)
            ->setTarget($target);
        $args = array('arg1','arg2');

        $this->assertEquals('arg1',$events->call($ev,$args,null));
        $result = array(
        );
        $this->assertEquals($result,$test->log);
    }

    /**
     * @expectedException        Rindow\Event\Exception\InvalidArgumentException
     * @expectedExceptionMessage invalid terminator callback on event "ev"
     */
    public function testEventManagerIllegalTerminator()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            $result = $invocation->proceed($arg1,$arg2);
            $test->log[] = 'ev1 back:'.$arg1;
            return $result;
        });

        $parameters = array(0=>'In','arg1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $ev = new Event();
        $ev->setName('ev')
            ->setParameters($parameters)
            ->setTarget($target);
        $args = array('arg1','arg2');
        $terminator = array('IllegalTerminator');

        $events->call($ev,$args,$terminator);
    }

    public function testEventManagerWithServiceLocator()
    {
        $container = new Container(array(
            'components' => array(
                __NAMESPACE__ . '\Logger'=>array(),
                __NAMESPACE__ . '\Ev1'=>array(),
                __NAMESPACE__ . '\Ev2'=>array(),
                __NAMESPACE__ . '\Ev1Callable'=>array(),
                __NAMESPACE__ . '\Ev2Callable'=>array(),
            ),
        ));
        $events = new EventManager();
        $events->setServiceLocator($container);
        $events->attach('ev',new EventListener(null, __NAMESPACE__ . '\Ev1','invoke'));
        $events->attach('ev',new EventListener(null, __NAMESPACE__ . '\Ev2','invoke'));
        $events->attach('ev',__NAMESPACE__ . '\Ev1Callable');
        $events->attach('ev',__NAMESPACE__ . '\Ev2Callable');
        $logger = $container->get( __NAMESPACE__ . '\Logger');

        $terminator = function ($event,$arg1,$arg2) use ($logger) {
            if($arg1!=='In'||$arg2!=='arg2')
                throw new \Exception("Error");
            $logger->log('orig:'.$arg1);
            return 'Out';
        };

        $this->assertEquals('Out',$events->call('ev',array('In','arg2'),$terminator));
        $log = $logger->getLog();
        $this->assertEquals('ev1 front:In',$log[0]);
        $this->assertEquals('ev2 front:In',$log[1]);
        $this->assertEquals('ev1callable front:In',$log[2]);
        $this->assertEquals('ev2callable front:In',$log[3]);
        $this->assertEquals('orig:In',$log[4]);
        $this->assertEquals('ev2callable back:Out',$log[5]);
        $this->assertEquals('ev1callable back:Out',$log[6]);
        $this->assertEquals('ev2 back:Out',$log[7]);
        $this->assertEquals('ev1 back:Out',$log[8]);
    }

    public function testEventNotFound()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();

        $event = new Event();
        $event->setName('ev');

        $terminator = function ($event,$arg1,$arg2) use ($test) {
            $test->log[] = 'orig:'.$arg1;
            return 'Out';
        };

        $out = $events->call($event,array('In','arg2'),$terminator);
        $this->assertEquals('Out',$out);
        $this->assertEquals('orig:In',$test->log[0]);
    }

    public function testBreakProceeding()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            $test->log[] = 'ev1 front:'.$arg1;
            return $result = 'break by ev1';
            // Dont call invocation proced!!!
            //     $result = $invocation->proceed($arg1,$arg2);
            //     $test->log[] = 'ev1 back:'.$result;
        });
        $events->attach('ev',function (EventProceeding $invocation,$arg1,$arg2) use ($test) {
            $test->log[] = 'ev2 front:'.$arg1;
            $result = $invocation->proceed($arg1,$arg2);
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $terminator = function ($event,$arg1,$arg2) use ($test) {
            $test->log[] = 'orig:'.$arg1;
            return 'Out';
        };

        $target = new \stdClass();
        $target->test = 'test';

        $this->assertEquals('break by ev1',$events->call('ev',array('In','arg2'),$terminator));
        $this->assertEquals('ev1 front:In',$test->log[0]);
        $this->assertFalse(isset($test->log[1]));
    }

}
