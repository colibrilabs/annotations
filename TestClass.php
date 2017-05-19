<?php

namespace ORM\Colibri
{
  
  /**
   * Class Column
   * @Annotation
   */
  class Column {
    
  }
}

namespace Om\ORM\Entity{
  
  /**
   * Class Column
   * @Annotation
   */
  class User {
    
  }
  
}

namespace A\B\C {
  
  use Colibri\Annotations\Annotation as Core;
  use Om\ORM\Entity as ORM;

  /**
   * Class TestClass
   * @package A\B\C
   * @Core\Property(name: PHP_OS, required: false)
   * @Core\Property(name=\DateTime::RFC850)
   * @Core\Property(name=PHP_SAPI, required=true)
   * @Core\Property(required=false, name="stewie.dev@gmail.com")
   * @Core\Property(name="test", required=true)
   * @Column({123, 456, 789}, test:1)
   * @Column(route: "/blog/:id", @Annotation\Property("test", @ORM\User({321, 321, 123, 12.3, 123, '/auth'})))
   * @Annotation\Property(userID:7, valid: true, req:{
   *   "className": ORM\User::class,
   *   "phpExtDir": PHP_EXTENSION_DIR,
   *   "format": \DateTime::RFC850
   *   "test":1, 2, 3,
   *   "sub":@ORM\User({321, 321, 123, 12.3, 123, '/auth'})
   * })
   */
  class TestClass
  {
    
    /**
     * @Enum({'F', 'M'})
     */
    public $user;
    
  }
}

