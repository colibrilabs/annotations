<?php

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
  use Colibri\Annotations\Annotation\Property;
  use Om\ORM\Entity as ORM;

  /**
   * Class TestClass
   * @package A\B\C
   * @Column(route: "/auth/json", test: {1, @Property()})
   *
   * @Column(name: @Target({Core\Target::PROPERTY}), required=false)
   *
   * @Column({123, 456, 789}, test:1)
   * @Column(route: "/blog/:id", @Column("test", @ORM\User({321, 321, 123, 12.3, 123, '/auth'})))
   * @Column(userID:7, valid: true, req:{
   *   "phpExtDir": PHP_EXTENSION_DIR,
   *   "format": \DateTime::RFC850
   *   "test":1, 2, 3, @Annotation(),
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

