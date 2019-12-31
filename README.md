### Issues with loading Alice fixtures with nelmio/alice ^3.5.8 
Hey, we're slowly upgrading to alice 3, which in our case means the latest alice 3.5.8 and 
we're experiencing some issues with this new version that seem to relate to creating objects using `__calls:`
while the object used in `__calls` has been initialised using the same object.

Either way, the `__calls` function seem to create entirely new objects using the referenced template,
instead of being the object created from that template.

## An example:


Given the following class:
```php
class Entity
{
    private $name;
    private $ytitne;

    public function __construct(string $name, Ytitne $ytitne)
    {
        $this->name   = $name;
        $this->ytitne = $ytitne;
        $this->ytitne->alterEntity($this);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```
Which gets an instance of `Ytitne` and calls it's `alterEntity` function.
And the `Ytitne` class:
```php
class Ytitne
{
    private $name;
    private $entity;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function alterEntity(Entity $entity): void
    {
        if (null !== $this->entity && $this->entity !== $entity) {
            var_dump($this->entity->getName(), $entity->getName());
            throw new \InvalidArgumentException('Can not alter this entity, as one exists');
        }
        $this->entity = $entity;
    }
}
```


Note, the `alterEntity` function will throw an exception when the `Entity` has already been set
and the given `Entity` is not the same as the one already configured.

Creating a fixture file for this, we get the following `broken.yaml`
```yaml
VanNes\Entity:
    entity_1:
        __construct:
            - <word()>
            - '@ytitne_1'


VanNes\Ytitne:
    ytitne_1:
        __construct:
            - <word()>
        __calls:
            -  alterEntity: ['@entity_1']
```

This would imply that two objects are created, and the `ytitne_1` calls the `alterEntity`
function with the created object `@entity_1`.

Instead, if we use the following code to load this fixture file we get a completely different result.
```php
use Nelmio\Alice\Loader\NativeLoader;

$loader = new NativeLoader();
$loader->loadFile(__DIR__ . '/Resources/broken.yaml');
```

Namely, we get the following: 
```
/home/mvannes/projects/alice-loading-issue/src/Ytitne.php:19:
string(8) "quisquam"
/home/mvannes/projects/alice-loading-issue/src/Ytitne.php:19:
string(5) "culpa"
PHP Fatal error:  Uncaught InvalidArgumentException: Can not alter this entity, as one exists in /home/mvannes/projects/alice-loading-issue/src/Ytitne.php:20
Stack trace:
#0 /home/mvannes/projects/alice-loading-issue/src/Entity.php(15): VanNes\Ytitne->alterEntity(Object(VanNes\Entity))
#1 /home/mvannes/projects/alice-loading-issue/vendor/nelmio/alice/src/Generator/Instantiator/Chainable/NoCallerMethodCallInstantiator.php(41): VanNes\Entity->__construct('culpa', Object(VanNes\Ytitne))
#2 /home/mvannes/projects/alice-loading-issue/vendor/nelmio/alice/src/Generator/Instantiator/Chainable/AbstractChainableInstantiator.php(44): Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator->createInstance(Object(Nelmio\Alice\Definition\Fixture\TemplatingFixture))
#3 /home/mvannes/projects/alice-loading-issue/vendor/nelmio/alice/src/Generator/Instantiator/InstantiatorRegistry.php(72): Nelmio\Alice\Generator\Instantiator\Chainable\AbstractChainableInstantiator->instantiate(Object(Nelmio\Alice\Definition\Fixture\Templati in /home/mvannes/projects/alice-loading-issue/vendor/nelmio/alice/src/Generator/ObjectGenerator/SimpleObjectGenerator.php on line 114
```

As one can see, the `\InvalidArgumentException` is thrown. And the object passed to `alterEntity` even has a different
name from the one that was initially set through the `Entity` constructor (or vice versa?)

If we remove the call to this exception, replace it with a `return`, and instead look at a var dump of the created objects, we get the following:

```
array(2) {
  'ytitne_1' =>
  class VanNes\Ytitne#274 (2) {
    private $name =>
    string(11) "consequatur"
    private $entity =>
    class VanNes\Entity#290 (2) {
      private $name =>
      string(8) "quisquam"
      private $ytitne =>
              ...

    }
  }
  'entity_1' =>
  class VanNes\Entity#279 (2) {
    private $name =>
    string(5) "culpa"
    private $ytitne =>
    class VanNes\Ytitne#274 (2) {
      private $name =>
      string(11) "consequatur"
      private $entity =>
      class VanNes\Entity#290 (2) {
        ...
      }
    }
  }
}

```

Note that the object hashes of the `entity_1` and `ytitne_1`'s entity differ.

This feels like it isn't intended behaviour, as additional objects are created instead of 

To test it for yourself; 
- `git clone git@github.com:Mvannes/alice-loading-issue.git`
- `cd alice-loading-issue`
- `composer install`
- `php app.php`


This was tested using the following php version:
```
PHP 7.3.9-1+ubuntu18.04.1+deb.sury.org+1 (cli) (built: Sep  2 2019 12:54:24) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.9, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.3.9-1+ubuntu18.04.1+deb.sury.org+1, Copyright (c) 1999-2018, by Zend Technologies
    with Xdebug v2.7.2, Copyright (c) 2002-2019, by Derick Rethans

```
