<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:22 下午
 */

namespace Yarfox\Container\Contract;

use Yarfox\Container\Constant\Scope;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Container Interface.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * register producer.
     * @param string $key
     * @param mixed $producer class or closure( function (ContainerInterface $container) )
     * @param string $scope
     * @return void
     */
    public function registerProducer(string $key, mixed $producer, string $scope = Scope::SCOPE_PROTOTYPE): void;

    /**
     * @param string $key
     * @param mixed $producer
     * @return void
     */
    public function registerSingletonProducer(string $key, mixed $producer): void;

    /**
     * @param string $key
     * @param mixed $producer
     * @return void
     */
    public function registerRequestProducer(string $key, mixed $producer): void;

    /**
     * get producer.
     * @param string $key
     * @return mixed
     */
    public function getProducer(string $key): mixed;

    /**
     * register instance.
     * @param string $key
     * @param object $instance
     * @param string $scope
     * @return void
     */
    public function registerInstance(string $key, object $instance, string $scope = Scope::SCOPE_GLOBAL): void;

    /**
     * get instance.
     * @param string $key
     * @param bool $throwException
     * @return mixed
     */
    public function getInstance(string $key, bool $throwException = false): mixed;

    /**
     * @param string $key
     * @return mixed
     */
    public function resolve(string $key): mixed;

    /**
     * @param string $class
     * @return mixed
     */
    public function resolveClass(string $class): mixed;

    /**
     * register configs.
     * @param array $configs
     * @param string $scope
     * @return void
     */
    public function registerConfigs(array $configs, string $scope = Scope::SCOPE_GLOBAL): void;

    /**
     * get all configs.
     * @return array
     */
    public function getConfigs(): array;

    /**
     * register config.
     * @param string $key
     * @param mixed $value
     * @param string $scope
     * @return void
     */
    public function registerConfig(string $key, mixed $value, string $scope = Scope::SCOPE_GLOBAL): void;

    /**
     * get config.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed;

    /**
     * @return void
     */
    public function reset(): void;

    /**
     * @return void
     */
    public function resetRequestScope(): void;
}
