# PHP Container

Another container in PHP.  

## Install

```shell
composer require anhoder/container
```

## Usage

```php
abstract class AA {}

class AAI extends AA{}

class A {
}
class B {
    public function __construct(A $a)
    {
    }
}
class C {
    public function __construct(B $b, int $a = 123)
    {
    }
}
class D {
    public function __construct(A $a, AA $b)
    {}
}

$container = new Container();
$container->registerProducer('a', function () {
    return new A();
});
$container->resolve('a'); // get A()
$container->resolve(A::class); // get A()
$container->resolve(C::class); // get C()
$container->resolve(D::class); // throw Container Exception: AA is not instantiable!

$container->registerInstance(AA::class, $container->resolveClass(AAI::class)); // AA::class => AAI()
$container->resolve(D::class); // get D()
```


