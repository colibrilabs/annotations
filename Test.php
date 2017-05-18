<?php

namespace TestBoard;

use Colibri\Annotations\AnnotationLoader;
use Colibri\Annotations\Parser;

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/TestClass.php';

error_reporting(E_ALL); ini_set('display_errors', 'On');

AnnotationLoader::registerAutoloadDirectories([
  __DIR__ . '/src/Annotation'
]);

$reflectionClass = new \ReflectionClass(\A\B\C\TestClass::class);

try {
  
  $parser = new Parser();
  $parser->addNamespace('Colibri\\Annotations');
  $parser->addNamespace('ORM\\Colibri');
  $parser->addNamespace('Om\\ORM\\Entity');
  
  $parser->addAliasOf('Colibri\\Annotations\\Annotation', 'Ann');
  $parser->addAliasOf('Om\\ORM\\Entity', 'ORM');

  var_dump($parser->parse(trim($reflectionClass->getDocComment(), '/')));
  
} catch (\Throwable $exception) {
  echo sprintf('%s [%s]', get_class($exception), $exception->getMessage()), PHP_EOL, $exception->getTraceAsString(); die();
}
