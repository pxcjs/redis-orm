Usage
=====

First, you will need to define some mapping metadata on your models:

```PHP

<?php

use Tystr\RedisOrm\Annotations\Id;
use Tystr\RedisOrm\Annotations\Index;
use Tystr\RedisOrm\Annotations\Date;
use Tystr\RedisOrm\Annotations\Field;

/**
 * @Prefix("cars")
 */
class Car
{
    /**
     * @var int
     * @Field(type="integer")
     * @Id
     */
    protected $id;
    
    /**
     * @var string
     * @Field(type="string")
     * @Index
     */
    protected $name;
    
    /**
     * @var \DateTime
     * @Field(type="date")
     * @Date
     */
    protected $createdAt;

    /**
     * @param int       $id
     * @param string    $name
     * @param \DateTime $createdAt
     */
    public function __construct($id, $name, \DateTime $createdAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->createdAt = $createdAt;
    }
    // Getters and Setters
}

```
## Saving Objects

Now, the repository class will know which indexes to create and what to use as the values.

Instantiating the `ObjectRepository` class and saving your object is simple:
```PHP
<?php

require 'vendor/autoload.php';

$redis = new Predis\Client();
$keyNamingStrategy = new Tystr\RedisOrm\KeyNamingStrategy\ColonDelimitedKeyNamingStrategy();
$repository = new ObjectRepository($client, $keyNamingStrategy));

$car = new Car(123, 'Tesla', new \DateTime());

$repository->save($car);
```

## Finding Objects
```PHP
<?php

$criteria = new Tystr\RedisOrm\Criteria\Criteria();
$criteria->addRestriction(Tystr\RedirOrm\Criteria\Restrictions::equalTo('id', '123));

// This returns an array of hydrated objects that match, or an empty array if no matches are found
$results = $repository->findBy($criteria);
```

Next: [Annotations](03-annotations.md)