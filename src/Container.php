<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:11 下午
 */

namespace Yarfox\Container;

use NotFoundException;
use Yarfox\Container\Constant\Constant;
use Yarfox\Container\Contract\ContainerInterface;
use Yarfox\Container\Exception\ContainerException;
use ReflectionClass;
use Psr\Container\ContainerInterface as PsrContainerInterface;

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
    private array $configs = [];

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
            static::$instance->reset();
        }

        return static::$instance;
    }

    /**
     * @param string $key
     * @param mixed $producer
     * @param string $scope
     */
    public function registerProducer(string $key, mixed $producer, string $scope = Constant::SCOPE_PROTOTYPE): void
    {
        if (!$producer) return;
        $this->producers[$key] = $producer;

        $scope == Constant::SCOPE_SINGLETON && $this->singletons[$key] = true;
    }

    /**
     * @param string $key
     * @param mixed $producer
     */
    public function registerSingletonProducer(string $key, mixed $producer): void
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
    public function registerInstance(string $key, object $instance): void
    {
        $this->instances[$key] = $instance;
    }

    /**
     * @param string $key
     * @param bool $throwException
     * @return mixed
     * @throws \NotFoundException
     * @throws \ReflectionException
     */
    public function getInstance(string $key, bool $throwException = false): mixed
    {
        if (isset($this->instances[$key]))
            return $this->instances[$key];

        $instance = $this->resolve($key);
        if ($instance && isset($this->singletons[$key]) && $this->singletons[$key]) {
            $this->registerInstance($key, $instance);
        }

        if (!$instance && $throwException) {
            throw new NotFoundException();
        }

        return $instance;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \ReflectionException|NotFoundException
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

        return $this->getInstance($producer);
    }

    /**
     * @param string $class
     * @return mixed
     * @throws \ReflectionException|NotFoundException
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
            if (is_null($type) || (!class_exists($type->getName()) && !interface_exists($type->getName()))) {
                if ($param->isDefaultValueAvailable()) {
                    break;
                }
                return null;
            }

            if ($type->getName() == $class) {
                throw new ContainerException("Class {$class} depend on itself!");
            }

            $instance = $this->getInstance($type->getName());
            if (!$instance) {
                if ($param->isDefaultValueAvailable()) {
                    break;
                }
                return null;
            }

            $constructorParams[] = $instance;
        }

        return $reflectionClass->newInstanceArgs($constructorParams);
    }

    /**
     * @param array $configs
     */
    public function registerConfigs(array $configs): void
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
    public function registerConfig(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $existsKeys = [];
        $config = &$this->configs;
        foreach ($keys as $key) {

            if (!is_array($config)) {
                $key = implode('.', $existsKeys);
                throw new ContainerException("Config key({$key}) already exists!");
            }

            if (array_key_exists($key, $config)) {
                $existsKeys[] = $key;

                if (is_null($config[$key])) {
                    $key = implode('.', $existsKeys);
                    throw new ContainerException("Config key({$key}) already exists!");
                }
            } else {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }

        $config = $value;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $config = $this->configs;

        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                return $default;
            }

            $config = $config[$key];
        }

        return $config;
    }

    public function reset(): void
    {
        $container = static::$instance;
        $container->instances = [];
        $container->producers = [];
        $container->singletons = [];
        $container->configs = [];

        $container->registerInstance(ContainerInterface::class, static::$instance);
        $container->registerInstance(PsrContainerInterface::class, static::$instance);
    }

    /**
     * @param string $id
     * @return object|null
     * @throws \ReflectionException|NotFoundException
     */
    public function get(string $id): ?object
    {
        return $this->getInstance($id);
    }

    /**
     * @param string $id
     * @return bool
     * @throws \ReflectionException|NotFoundException
     */
    public function has(string $id): bool
    {
        return !empty($this->getInstance($id));
    }
}
