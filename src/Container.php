<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:11 下午
 */

namespace Yarfox\Container;

use NotFoundException;
use ReflectionException;
use Yarfox\Container\Constant\Scope;
use Yarfox\Container\Contract\ContainerInterface;
use Yarfox\Container\Contract\ProducerInterface;
use Yarfox\Container\Exception\ContainerException;
use ReflectionClass;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     * @example [scope => []]
     */
    private array $instances;

    /**
     * @var array
     * @example [scope => []]
     */
    private array $producers;

    /**
     * @var array
     * @example [scope => [key => true]]
     */
    private array $scopes;

    /**
     * @var array
     * @example [scope => []]
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
    public function registerProducer(string $key, mixed $producer, string $scope = Scope::SCOPE_PROTOTYPE): void
    {
        if (!$producer || !isset(Scope::SCOPES[$scope])) return;

        $this->producers[$scope][$key] = $producer;

        if (isset(Scope::CACHEABLE_SCOPES[$scope])) {
            $this->scopes[$scope][$key] = true;
        }
    }

    /**
     * @param string $key
     * @param mixed $producer
     */
    public function registerSingletonProducer(string $key, mixed $producer): void
    {
        $this->registerProducer($key, $producer, Scope::SCOPE_GLOBAL);
    }

    /**
     * @param string $key
     * @param mixed $producer
     * @return void
     */
    public function registerRequestProducer(string $key, mixed $producer): void
    {
        $this->registerProducer($key, $producer, Scope::SCOPE_REQUEST);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getProducer(string $key): mixed
    {
        return $this->producers[Scope::SCOPE_REQUEST][$key] ?? $this->producers[Scope::SCOPE_GLOBAL][$key] ?? $this->producers[Scope::SCOPE_PROTOTYPE][$key] ?? null;
    }

    /**
     * @param string $key
     * @param object $instance
     * @param string $scope
     */
    public function registerInstance(string $key, object $instance, string $scope = Scope::SCOPE_GLOBAL): void
    {
        if (!isset(Scope::CACHEABLE_SCOPES[$scope])) return;

        $this->instances[$scope][$key] = $instance;
    }

    /**
     * @param string $key
     * @param bool $throwException
     * @return mixed
     * @throws NotFoundException|ReflectionException
     */
    public function getInstance(string $key, bool $throwException = false): mixed
    {
        $instance = $this->instances[Scope::SCOPE_REQUEST][$key] ?? $this->instances[Scope::SCOPE_GLOBAL][$key] ?? null;
        if ($instance) {
            return $instance;
        }

        $scope = match (true) {
            isset($this->scopes[Scope::SCOPE_REQUEST]) => Scope::SCOPE_REQUEST,
            isset($this->scopes[Scope::SCOPE_GLOBAL]) => Scope::SCOPE_GLOBAL,
            default => Scope::SCOPE_PROTOTYPE,
        };

        $instance = $this->resolve($key);
        if ($instance && isset(Scope::CACHEABLE_SCOPES[$scope])) {
            $this->registerInstance($key, $instance, $scope);
        }

        if (!$instance && $throwException) {
            throw new NotFoundException();
        }

        return $instance;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \NotFoundException
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
            throw new ContainerException("The $key producer is itself!");
        }

        if (is_callable($producer)) {
            $instance = $producer($this);
            if (!$instance) {
                return null;
            }

            if ($instance == $key) {
                throw new ContainerException("The $key producer return itself!");
            }

            if (is_object($instance)) {
                return $instance;
            }

            $producer = $instance;
        } elseif ($producer instanceof ProducerInterface) {
            $instance = $producer->produce();
            if (!$instance) {
                return null;
            }

            return $instance;
        }

        return $this->getInstance($producer);
    }

    /**
     * @param string $class
     * @return mixed
     * @throws ReflectionException|NotFoundException
     */
    public function resolveClass(string $class): mixed
    {
        if (!class_exists($class)) {
            return null;
        }
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class $class is not instantiable!");
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
                throw new ContainerException("Class $class depend on itself!");
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
     * @param string $scope
     */
    public function registerConfigs(array $configs, string $scope = Scope::SCOPE_GLOBAL): void
    {
        if (!isset(Scope::CACHEABLE_SCOPES[$scope])) return;

        $this->configs[$scope] = $configs;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return array_replace_recursive($this->configs[Scope::SCOPE_GLOBAL] ?? [], $this->configs[Scope::SCOPE_REQUEST] ?? []);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $scope
     */
    public function registerConfig(string $key, mixed $value, string $scope = Scope::SCOPE_GLOBAL): void
    {
        $keys = explode('.', $key);
        $existsKeys = [];

        $config = [];
        if (isset(Scope::CACHEABLE_SCOPES[$scope])) {
            $this->configs[$scope] = $this->configs[$scope] ?? [];
            $config = &$this->configs[$scope];
        }

        foreach ($keys as $key) {

            if (!is_array($config)) {
                $key = implode('.', $existsKeys);
                throw new ContainerException("Config key($key) already exists!");
            }

            if (array_key_exists($key, $config)) {
                $existsKeys[] = $key;

                if (is_null($config[$key])) {
                    $key = implode('.', $existsKeys);
                    throw new ContainerException("Config key($key) already exists!");
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
        $globalConfigs = $this->configs[Scope::SCOPE_GLOBAL] ?? [];
        $requestConfigs = $this->configs[Scope::SCOPE_REQUEST] ?? [];
        $configs = array_replace_recursive($globalConfigs, $requestConfigs);

        foreach ($keys as $key) {
            if (!isset($configs[$key])) {
                return $default;
            }

            $configs = $configs[$key];
        }

        return $configs;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $container = static::$instance;
        $container->instances = [];
        $container->producers = [];
        $container->scopes = [];
        $container->configs = [];

        $container->registerInstance(ContainerInterface::class, static::$instance);
        $container->registerInstance(PsrContainerInterface::class, static::$instance);
    }

    /**
     * @return void
     */
    public function resetRequestScope(): void
    {
        $container = static::$instance;
        unset(
            $container->instances[Scope::SCOPE_REQUEST],
            $container->producers[Scope::SCOPE_REQUEST],
            $container->scopes[Scope::SCOPE_REQUEST],
            $container->configs[Scope::SCOPE_REQUEST],
        );
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
