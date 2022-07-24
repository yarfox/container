<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 6:42 下午
 */

namespace Yarfox\Container\Test;

use Yarfox\Container\Constant\Scope;
use Yarfox\Container\Facade\Container;
use Yarfox\Container\Exception\ContainerException;
use PHPUnit\Framework\TestCase;
use Yarfox\Container\Container as RealContainer;

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
     * @covers \Yarfox\Container\Container::registerInstance
     * @covers \Yarfox\Container\Container::getInstance
     */
    public function testRegisterInstance()
    {
        $container = $this->newContainer();
        $container->registerInstance('a', new A());
        $this->assertInstanceOf(A::class, $container->getInstance('a'));
        $this->assertInstanceOf(A::class, Container::getInstance('a'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::resolve
     */
    public function testResolve()
    {
        $container = $this->newContainer();
        $container->registerProducer('a', function () {
            return new A();
        });
        $this->assertInstanceOf(A::class, $container->resolve('a'));
        $this->assertInstanceOf(A::class, $container->resolve(A::class));
        $this->assertInstanceOf(C::class, $container->resolve(C::class));
        $this->assertInstanceOf(A::class, Container::resolve('a'));
        $this->assertInstanceOf(A::class, Container::resolve(A::class));
        $this->assertInstanceOf(C::class, Container::resolve(C::class));
        $this->expectException(ContainerException::class);
        $container->resolve(D::class);
    }

    /**
     * @covers \Yarfox\Container\Container::resolveClass
     * @covers \Yarfox\Container\Container::registerInstance
     * @covers \Yarfox\Container\Container::resolve
     */
    public function testResolveClass()
    {
        $container = $this->newContainer();
        $container->registerInstance(AA::class, $container->resolveClass(AAI::class));
        $this->assertInstanceOf(D::class, $container->resolve(D::class));
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::getInstance
     * @covers \Yarfox\Container\Container::resolve
     */
    public function testRegisterProducer()
    {
        $container = $this->newContainer();
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
     * @covers \Yarfox\Container\Container::registerConfigs
     * @covers \Yarfox\Container\Container::getConfig
     */
    public function testRegisterConfigs()
    {
        $container = $this->newContainer();
        $container->registerConfigs(['a' => 'b']);
        $this->assertEquals('b', $container->getConfig('a'));
        $this->assertEquals(null, $container->getConfig('b'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::getProducer
     */
    public function testGetProducer()
    {
        $container = $this->newContainer();
        $container->registerProducer(A::class, A::class);
        $container->registerProducer('b_class', fn() => B::class);
        $this->assertEquals(A::class, $container->getProducer(A::class));
        $this->assertIsCallable($container->getProducer('b_class'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerConfig
     * @covers \Yarfox\Container\Container::getConfig
     */
    public function testRegisterConfig()
    {
        $container = $this->newContainer();
        $container->registerConfig('a', 'b');
        //$container->registerConfig('b.c', null);
        $container->registerConfig('b.c.d', 'e');
        $this->assertEquals('b', $container->getConfig('a'));
        $this->assertEquals('e', $container->getConfig('b.c.d'));
        $this->assertEquals(['c' => ['d' => 'e']], $container->getConfig('b'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerConfig
     * @covers \Yarfox\Container\Container::getConfigs
     */
    public function testRegisterSingletonProducer()
    {
        $container = $this->newContainer();
        $container->registerSingletonProducer('b_class', fn() => new B(new A()));
        $this->assertEquals(spl_object_hash($container->getInstance('b_class')), spl_object_hash($container->getInstance('b_class')));
    }

    /**
     * @covers \Yarfox\Container\Container::registerConfig
     * @covers \Yarfox\Container\Container::getConfigs
     */
    public function testGetConfigs()
    {
        $container = $this->newContainer();
        $container->registerConfig('a', 'b');
        $this->assertEquals(['a' => 'b'], $container->getConfigs());
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::get
     */
    public function testGet()
    {
        $container = $this->newContainer();
        $container->registerProducer('a_class', A::class);
        $this->assertInstanceOf(A::class, $container->get('a_class'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::has
     */
    public function testHas()
    {
        $container = $this->newContainer();
        $container->registerProducer('a_class', A::class);
        $this->assertTrue($container->has('a_class'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::getInstance
     */
    public function testProducer()
    {
        $container = $this->newContainer();
        $container->registerProducer('test', new ExampleProducer());
        $test = $container->getInstance('test');
        $this->assertEquals('test', $test->test());
    }

    /**
     * @covers \Yarfox\Container\Container::registerProducer
     * @covers \Yarfox\Container\Container::getInstance
     */
    public function testRegisterProducerByScope()
    {
        $container = $this->newContainer();
        $container->registerProducer('a', fn() => new B(new A()));
        $container->registerProducer('a', fn() => new A(), Scope::SCOPE_REQUEST);
        $container->registerProducer('a', fn() => new AAI(), Scope::SCOPE_GLOBAL);
        $this->assertInstanceOf(A::class, $container->getInstance('a'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerInstance
     * @covers \Yarfox\Container\Container::getInstance
     */
    public function testRegisterInstanceByScope()
    {
        $container = $this->newContainer();
        $container->registerInstance('a', new B(new A()), Scope::SCOPE_PROTOTYPE);
        $container->registerInstance('a', new A(), Scope::SCOPE_REQUEST);
        $container->registerInstance('a', new AAI(), Scope::SCOPE_GLOBAL);
        $this->assertInstanceOf(A::class, $container->getInstance('a'));
    }

    /**
     * @covers \Yarfox\Container\Container::registerConfigs
     * @covers \Yarfox\Container\Container::getConfigs
     */
    public function testRegisterConfigsByScope()
    {
        $container = $this->newContainer();
        $container->registerConfigs(['name' => 'prototype'], Scope::SCOPE_PROTOTYPE);
        $container->registerConfigs(['name' => 'global'], Scope::SCOPE_GLOBAL);
        $this->assertEquals(['name' => 'global'], $container->getConfigs());
        $container->registerConfigs(['name' => 'request'], Scope::SCOPE_REQUEST);
        $this->assertEquals(['name' => 'request'], $container->getConfigs());

        $container->registerConfigs(['k1' => ['k2' => 'v2'], 'k3' => 'v3', 'k4' => 'v4'], Scope::SCOPE_GLOBAL);
        $container->registerConfigs(['k1' => ['k2' => 'v2q'], 'k3' => 'v3q', 'k5' => 'v5q'], Scope::SCOPE_REQUEST);
        $this->assertEquals(['k1' => ['k2' => 'v2q'], 'k3' => 'v3q', 'k4' => 'v4', 'k5' => 'v5q'], $container->getConfigs());
    }

    /**
     * @covers \Yarfox\Container\Container::registerConfigs
     * @covers \Yarfox\Container\Container::registerConfig
     * @covers \Yarfox\Container\Container::getConfigs
     * @covers \Yarfox\Container\Container::getConfig
     */
    public function testRegisterConfigByScope()
    {
        $container = $this->newContainer();
        $container->registerConfigs(['name' => 'global'], Scope::SCOPE_GLOBAL);
        $this->assertEquals('global', $container->getConfig('name'));
        $container->registerConfig('foo.bar', 'global_bar', Scope::SCOPE_REQUEST);
        $this->assertEquals(null, $container->getConfig('foo1'));

        $container->registerConfig('name', 'request', Scope::SCOPE_REQUEST);
        $container->registerConfig('foo.bar', 'bar', Scope::SCOPE_REQUEST);
        $container->registerConfig('foo1', 'bar1', Scope::SCOPE_REQUEST);
        $this->assertEquals('request', $container->getConfig('name'));
        $this->assertEquals('bar', $container->getConfig('foo.bar'));
        $this->assertEquals('bar1', $container->getConfig('foo1'));
        $this->assertEquals(['name' => 'request', 'foo' => ['bar' => 'bar'], 'foo1' => 'bar1'], $container->getConfigs());
    }

    public function newContainer(): RealContainer
    {
        $container = Container::instance();
        $container->reset();

        return $container;
    }
}
