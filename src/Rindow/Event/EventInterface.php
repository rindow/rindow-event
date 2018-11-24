<?php
namespace Rindow\Event;

interface EventInterface
{
    public function setName($name);
    public function getName();
    public function setTarget($target);
    public function getTarget();
    public function setBreak($status);
    public function getBreak();
    public function getPreviousResult();
    public function setPreviousResult($previousResult);
    public function getParameters();
    public function getParameter($name,$default=null);
    public function setParameters(array $args);
    public function setParameter($name,$value);
}
