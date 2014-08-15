<?php
namespace Tystr\RedisOrm\Context;

use Behat\Gherkin\Node\TableNode;
use Tystr\RedisOrm\KeyNamingStrategy\ColonDelimitedKeyNamingStrategy;
use Tystr\RedisOrm\Repository\ObjectRepository;
use Tystr\RedisOrm\Test\Model\Car;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class MainContext extends BaseContext
{
    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var array|Car[]
     */
    protected $cars = array();

    public function __construct()
    {
        parent::__construct();

        $keyNamingStrategy = new ColonDelimitedKeyNamingStrategy();
        $this->repository = new ObjectRepository(
            $this->redis,
            $keyNamingStrategy,
            'Tystr\RedisOrm\Test\Model\Car',
            'cars'
        );
    }

    /**
     * @Given /the following Cars?:/
     */
    public function theFollowingCars(TableNode $table)
    {
        $i = 1;
        foreach ($table->getHash() as $data) {
            $car = new Car();
            $car->setId($i);
            $car->setColor($data['color']);
            $car->setEngineType($data['engine_type']);
            $car->setMake($data['make']);
            $car->setModel($data['model']);
            $car->setManufactureDate(new \DateTime('2013-01-01'));
            $this->repository->save($car);
            $i++;
        }
    }

    /**
     * @Then there should be :count keys in the database
     */
    public function thereShouldBeKeysInTheDatabase($count)
    {
        assertCount($count, $this->redis->keys('*'));
    }

    /**
     * @Then the following keys should exist:
     */
    public function theFollowingKeysShouldExist(TableNode $table)
    {
        foreach ($table->getHash() as $key) {
            assertTrue($this->redis->sismember($key['name'], $key['value']));
        }
    }

    /**
     * @When I find a Car by id :id
     */
    public function iFindACarById($id)
    {
        $this->cars[] = $this->repository->find($id);
    }

    /**
     * @Then there should be :count car
     */
    public function iThereShouldBeCarReturned($count)
    {
        assertCount($count, $this->cars);
        assertInstanceOf('Tystr\RedisOrm\Test\Model\Car', $this->cars[0]);
    }

    /**
     * @Then the car with the id :arg1 should have the following properties:
     */
    public function theCarWithTheIdShouldHaveTheFollowingProperties($id, TableNode $table)
    {
        $car = $this->getObjectById($id);

        $expected = $table->getHash();
        assertEquals($expected[0]['make'], $car->getMake());
        assertEquals($expected[0]['model'], $car->getModel());
        assertEquals($expected[0]['engine_type'], $car->getEngineType());
        assertEquals($expected[0]['color'], $car->getColor());
    }

    /**
     * @When I set the manufacture date to null
     */
    public function iSetTheManufactureDateToNull()
    {
        $car = $this->getObjectById(1);
        $car->setManufactureDate(null);
        $this->repository->save($car);
    }

    /**
     * @Then When I set the color for the car :id to :color
     */
    public function whenISetTheColorForTheCarTo($id, $color)
    {
        $car = $this->getObjectById($id);
        $color = $color == 'null' ? null : $color;
        $car->setColor($color);
        $this->repository->save($car);
    }

    /**
     * @Then there should be :count items in the :key key
     */
    public function thereShouldBeItemsInTheKey($count, $key)
    {
        $type = $this->redis->type($key);
        if ('set' == $type) {
            assertEquals($count, $this->redis->scard($key));
        } else {
            assertEquals($count, $this->redis->zcard($key));
        }
    }

    /**
     * @param int$id
     * @return object
     */
    public function getObjectById($id)
    {
        $object = $this->repository->find($id);
        assertNotNull($object);

        return $object;
    }
}
