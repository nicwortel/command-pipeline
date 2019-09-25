# Command Pipeline

An in-memory Command Pipeline implementation for PHP. A command pipeline handles
cross-cutting concerns in a CQRS application that works with command objects,
such as validation of commands, authorization, logging, etc.

Commands are processed in a linear process. The return value of each stage is
passed to the next. A stage can only prevent the next stage from being executed
by throwing an exception.

The concept of the command pipeline is loosely based on the
[Pipes and Filters](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PipesAndFilters.html)
pattern.

## Features

- Command validation
- Wrapping command handling in a Doctrine database transaction
- Dispatching emitted domain events to an event bus

## Usage

```php
$command = new MyCommand();
$command->foo = 'bar';

$commandPipeline->process($command);
```
