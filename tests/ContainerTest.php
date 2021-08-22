<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 6:42 下午
 */

namespace Anhoder\Container\Test;

use Anhoder\Container\Container;
use Anhoder\Container\Exception\ContainerException;
use PHPUnit\Framework\TestCase;
use Anhoder\Container\Facade\Container as ContainerFacade;

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

class ContainerTest extends TestCase
{

    /**
     * @covers \Anhoder\Container\Container::registerInstance
     * @covers \Anhoder\Container\Container::getInstance
     */
    public function testRegisterInstance()
    {
        $container = Container::instance();
        $container->registerInstance('a', new A());
        $this->assertInstanceOf(A::class, $container->getInstance('a'));
        $this->assertInstanceOf(A::class, ContainerFacade::getInstance('a'));
    }

    /**
     * @covers \Anhoder\Container\Container::registerProducer
     * @covers \Anhoder\Container\Container::resolve
     */
    public function testResolve()
    {
        $container = Container::instance();
        $container->registerProducer('a', function () {
            return new A();
        });
        $this->assertInstanceOf(A::class, $container->resolve('a'));
        $this->assertInstanceOf(A::class, $container->resolve(A::class));
        $this->assertInstanceOf(C::class, $container->resolve(C::class));
        $this->assertInstanceOf(A::class, ContainerFacade::resolve('a'));
        $this->assertInstanceOf(A::class, ContainerFacade::resolve(A::class));
        $this->assertInstanceOf(C::class, ContainerFacade::resolve(C::class));
        $this->expectException(ContainerException::class);
        $container->resolve(D::class);
    }

    /**
     * @covers \Anhoder\Container\Container::resolveClass
     * @covers \Anhoder\Container\Container::registerInstance
     * @covers \Anhoder\Container\Container::resolve
     */
    public function testResolveClass()
    {
        $container = Container::instance();
        $container->registerInstance(AA::class, $container->resolveClass(AAI::class));
        $this->assertInstanceOf(D::class, $container->resolve(D::class));
    }

    /**
     * @covers \Anhoder\Container\Container::registerProducer
     * @covers \Anhoder\Container\Container::getInstance
     * @covers \Anhoder\Container\Container::resolve
     */
    public function testRegisterProducer()
    {
        $container = Container::instance();
        $container->registerProducer('a', function () {
            return new A();
        });
        $container->registerProducer('a', fn() => 'b');
        $container->registerProducer('b', 'c');
        $container->registerProducer('c', A::class);
        $this->assertInstanceOf(A::class, $container->getInstance('a'));
        $this->assertInstanceOf(B::class, $container->getInstance(B::class));
        $this->assertInstanceOf(B::class, $container->resolve(B::class));
    }

    /**
     * @covers \Anhoder\Container\Container::registerConfigs
     * @covers \Anhoder\Container\Container::getConfig
     */
    public function testRegisterConfigs()
    {
        $container = Container::instance();
        $container->registerConfigs(['a' => 'b']);
        $this->assertEquals('b', $container->getConfig('a'));
        $this->assertEquals(null, $container->getConfig('b'));
    }

    /**
     * @covers \Anhoder\Container\Container::registerProducer
     * @covers \Anhoder\Container\Container::getProducer
     */
    public function testGetProducer()
    {
        $container = Container::instance();
        $container->registerProducer(A::class, A::class);
        $container->registerProducer('b_class', fn() => B::class);
        $this->assertEquals(A::class, $container->getProducer(A::class));
        $this->assertIsCallable($container->getProducer('b_class'));
    }

    /**
     * @covers \Anhoder\Container\Container::registerConfig
     * @covers \Anhoder\Container\Container::getConfig
     */
    public function testRegisterConfig()
    {
        $container = Container::instance();
        $container->registerConfig('a', 'b');
        $this->assertEquals('b', $container->getConfig('a'));
        $this->assertEquals(null, $container->getConfig('b'));
    }

    /**
     * @covers \Anhoder\Container\Container::registerConfig
     * @covers \Anhoder\Container\Container::getConfigs
     */
    public function testRegisterSingletonProducer()
    {
        $container = Container::instance();
        $container->registerSingletonProducer('b_class', fn() => new B(new A()));
        $this->assertEquals(spl_object_hash($container->getInstance('b_class')), spl_object_hash($container->getInstance('b_class')));
    }

    /**
     * @covers \Anhoder\Container\Container::registerConfig
     * @covers \Anhoder\Container\Container::getConfigs
     */
    public function testGetConfigs()
    {
        $container = Container::instance();
        $container->registerConfig('a', 'b');
        $this->assertEquals(['a' => 'b'], $container->getConfigs());
    }

    /**
     * @covers \Anhoder\Container\Container::registerProducer
     * @covers \Anhoder\Container\Container::get
     */
    public function testGet()
    {
        $container = Container::instance();
        $container->registerProducer('a_class', A::class);
        $this->assertInstanceOf(A::class, $container->get('a_class'));
    }

    /**
     * @covers \Anhoder\Container\Container::registerProducer
     * @covers \Anhoder\Container\Container::has
     */
    public function testHas()
    {
        $container = Container::instance();
        $container->registerProducer('a_class', A::class);
        $this->assertTrue($container->has('a_class'));
    }
}
