<?php

namespace TestBoard;

use Subapp\Annotations\Reader;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/TestClass.php';

error_reporting(E_ALL); ini_set('display_errors', 'On');

$reflection = new \ReflectionClass(\A\B\C\TestClass::class);

try {

  $reader = new Reader();
  $reader->getParser()->addNamespace('A\B\C');
    
  var_dump($reader->getClassAnnotations($reflection));
  
  echo 'METHOD', PHP_EOL;
  foreach ($reflection->getMethods() as $method) {
    var_dump($reader->getMethodAnnotations($method));
  }
  
  echo 'PROPERTY', PHP_EOL;
  foreach ($reflection->getProperties() as $property) {
    foreach ($reader->getPropertyAnnotations($property) as $annotation) {
      var_dump(get_class($annotation), $annotation);
    }
  }
  
} catch (\Throwable $exception) {
  echo sprintf('<pre><h3>%s [%s]</h3> <div>%s</div></pre>',
    get_class($exception), $exception->getMessage(), $exception->getTraceAsString()); die();
}
