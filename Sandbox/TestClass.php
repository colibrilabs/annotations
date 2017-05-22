<?php

namespace A\B\C;

use Colibri\Annotations\Annotation\Property;
use Colibri\Annotations\Annotation\Target;


/**
 * Class TestClass
 * @package A\B\C
 * @Column(route: "/auth/json", test: {1, @Property()})
 *
 * @Column(name: @Target({Target::PROPERTY}), required=false)
 *
 * @Column({123, 456, 789}, test:1)
 * @Column(route: "/blog/:id", @Column("test", @ORM\User({321, 321, 123, 12.3, 123, '/auth'})))
 * @Column(userID:7, valid: true, req:{
 *   "phpExtDir": PHP_EXTENSION_DIR,
 *   "format": \DateTime::RFC850,
 *   "format2": TestClass::test,
 *   "sub":@ORM\User({321, 321, 123, 12.3, 123, '/auth'})
 * })
 */
class TestClass
{
  
  const test = __FILE__;
  
  /**
   * @Enum({'F', 'M'})
   * @Property(name=\DateTime::RFC850)
   */
  public $user;
  
  /**
   * @Target({1})
   */
  public function test()
  {
    return __METHOD__;
  }
  
}

