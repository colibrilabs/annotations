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
  
  use Colibri\Annotations\Annotation as Ann;
  use Om\ORM\Entity as ORM;
  
//  * @Annotation\Property(name="test")
//  * @ORM\User({321, 321, 123, 12.3, 123, '/auth'})
// @Column(123, "/blog/:id", @Annotation\Property("test", @ORM\User({321, 321, 123, 12.3, 123, '/auth'})))
  // @Annotation\Property(userID = 7, test=@Annotation\Property(123), 321, 333, 111)
  
  
  /**
   * Class TestClass
   * @Annotation\Property(userID:7, valid:true, req:{test:1, 2, 3, sub:@ORM\User({321, 321, 123, 12.3, 123, '/auth'})})
   */
  class TestClass
  {
    
    /**
     * @var string
     */
    public $user;
    
  }
}

