# Command Pipeline

[![Build status](https://img.shields.io/travis/com/nicwortel/command-pipeline)](https://travis-ci.com/nicwortel/command-pipeline)
[![Required PHP version](https://img.shields.io/packagist/php-v/nicwortel/command-pipeline)](https://github.com/nicwortel/command-pipeline/blob/master/composer.json)

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
- Command authorization
- Wrapping command handling in a Doctrine database transaction
- Dispatching emitted domain events to an event bus

## Installation

```bash
composer require nicwortel/command-pipeline
```

If you are using Symfony, enable the CommandPipelineBundle:

```php
// config/bundles.php

return [
    // ...
    NicWortel\CommandPipeline\Bundle\CommandPipelineBundle::class => ['all' => true],
];
```

## Usage

Fetch the `command_pipeline` service from the service container or (recommended) inject the CommandPipeline into your
application code:

```php
$command = new MyCommand();
$command->foo = 'bar';

$commandPipeline->process($command);
```
