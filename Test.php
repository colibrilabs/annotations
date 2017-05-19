<?php

namespace TestBoard;

use Colibri\Annotations\Annotation\Target;
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
  $parser->addAliasOf('Colibri\\Annotations\\Annotation', 'Core');

  $parser->setTarget(Target::CLAZZ);
  $parser->setIgnoreNotImportedAnnotation(true);
  var_dump($parser->parse($reflection->getDocComment(), sprintf('class %s {}', $reflection->getName())));
  
  $reader = new Reader($parser);
  var_dump($reader->getClassAnnotations($reflection));
  
} catch (\Throwable $exception) {
  echo sprintf('<pre><h3>%s [%s]</h3> <div>%s</div></pre>', get_class($exception), $exception->getMessage(), $exception->getTraceAsString()); die();
}
