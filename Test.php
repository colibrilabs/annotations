<?php

namespace TestBoard;

use Colibri\Annotations\Parser;
use Colibri\Annotations\Reader;

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/TestClass.php';

error_reporting(E_ALL); ini_set('display_errors', 'On');

$reflection = new \ReflectionClass(\A\B\C\TestClass::class);

try {
  
  $parser = new Parser();
  $parser->addNamespace('Colibri\\Annotations');
  $parser->addNamespace('ORM\\Colibri');
  $parser->addNamespace('Om\\ORM\\Entity');
  $parser->addNamespace('Colibri\\Annotations\\Annotation');
  
  $parser->addAliasOf('Colibri\\Annotations\\Annotation', 'Core');
  $parser->addAliasOf('Om\\ORM\\Entity', 'ORM');
  
  $reader = new Reader($parser);
  var_dump($reader->getClassAnnotations($reflection));
  
} catch (\Throwable $exception) {
  echo sprintf('%s [%s]', get_class($exception), $exception->getMessage()), PHP_EOL, $exception->getTraceAsString(); die();
}
