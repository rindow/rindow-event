Event manager library
=====================
Master: [![Build Status](https://travis-ci.com/rindow/rindow-event.png?branch=master)](https://travis-ci.com/rindow/rindow-event)

The event manager realizes "observer pattern" in software design pattern.

It is an important library used in the core architecture of the Rindow Framework's AOP.

It is also used as a pipeline for other MVC models.

The following four classes are implemented.

- Listener instance manager(Observer)
- Event Manager(Subject)
- Event proceeding(Recursive event notification as part of subject)
- Event(Notified event)
