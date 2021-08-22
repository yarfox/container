<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 4:11 下午
 */

namespace Anhoder\Container;

use Anhoder\Container\Constant\Constant;
use Anhoder\Container\Contract\ContainerInterface;
use Anhoder\Container\Exception\ContainerException;
use ReflectionClass;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private array $instances;

    /**
     * @var array
     */
    private array $producers;

    /**
     * @var array
     */
    private array $singletons;

    /**
     * @var array
     */
    private array $configs;

    /**
     * @var ?static
     */
    private static ?self $instance = null;

    /**
     * Constructor of Container.
     */
    private function __construct()
    {}

    /**
     * @return static
     */
    public static function instance(): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new Container();
        }

        return static::$instance;
    }

    /**
     * @param string $key
     * @param mixed $producer
     * @param string $scope
     */
    public function registerProducer(string $key, mixed $producer, string $scope = Constant::SCOPE_PROTOTYPE)
    {
        if (!$producer) return;
        $this->producers[$key] = $producer;

        $scope == Constant::SCOPE_SINGLETON && $this->singletons[$key] = true;
    }

    /**
     * @param string $key
     * @param mixed $producer
     */
    public function registerSingletonProducer(string $key, mixed $producer)
    {
        $this->registerProducer($key, $producer, Constant::SCOPE_SINGLETON);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getProducer(string $key): mixed
    {
        return $this->producers[$key] ?? null;
    }

    /**
     * @param string $key
     * @param object $instance
     */
    public function registerInstance(string $key, object $instance)
    {
        $this->instances[$key] = $instance;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \ReflectionException
     */
    public function getInstance(string $key): mixed
    {
        if (isset($this->instances[$key]))
            return $this->instances[$key];

        $instance = $this->resolve($key);
        if (isset($this->singletons[$key]) && $this->singletons[$key]) {
            $this->registerInstance($key, $instance);
        }

        return $instance;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \ReflectionException
     */
    public function resolve(string $key): mixed
    {
        $producer = $this->getProducer($key);
        if (!$producer) {
            return $this->resolveClass($key);
        }

        if ($producer == $key) {
            $instance = $this->resolveClass($key);
            if ($instance) {
                return $instance;
            }
            throw new ContainerException("The {$key} producer is itself!");
        }

        if (is_callable($producer)) {
            $instance = $producer($this);
            if (!$instance) {
                return null;
            }

            if ($instance == $key) {
                throw new ContainerException("The {$key} producer return itself!");
            }

            if (is_object($instance)) {
                return $instance;
            }

            $producer = $instance;
        }

        $instance = $this->getInstance($producer);
        if (!$instance) {
            $instance = $this->resolveClass($producer);
        }

        return $instance;
    }

    /**
     * @param string $class
     * @return mixed
     * @throws \ReflectionException
     */
    public function resolveClass(string $class): mixed
    {
        if (!class_exists($class)) {
            return null;
        }
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class {$class} is not instantiable!");
        }
        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            return new $class();
        }

        $constructorParams = [];
        $params = $constructor->getParameters();
        foreach ($params as $param) {
            $type = $param->getType();
            if (is_null($type) || !class_exists($type->getName())) {
                if ($param->isDefaultValueAvailable()) {
                    break;
                }

                return null;
            }

            if ($type->getName() == $class) {
                throw new ContainerException("Class {$class} depend on itself!");
            }


            $instance = $this->getInstance($type->getName());
            if (!$instance) return null;

            $constructorParams[] = $instance;
        }

        return $reflectionClass->newInstanceArgs($constructorParams);
    }

    /**
     * @param array $configs
     */
    public function registerConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function registerConfig(string $key, mixed $value)
    {
        $this->configs[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->configs[$key] ?? $default;
    }

    /**
     * @param string $id
     * @return object|null
     * @throws \ReflectionException
     */
    public function get(string $id)
    {
        return $this->getInstance($id);
    }

    /**
     * @param string $id
     * @return bool
     * @throws \ReflectionException
     */
    public function has(string $id): bool
    {
        return !empty($this->getInstance($id));
    }
}
