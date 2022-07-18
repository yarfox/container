<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 4:22 下午
 */

namespace Yarfox\Container\Contract;

use Yarfox\Container\Constant\Constant;
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
    public function registerProducer(string $key, mixed $producer, string $scope = Constant::SCOPE_PROTOTYPE): void;

    /**
     * @param string $key
     * @param mixed $producer
     * @return void
     */
    public function registerSingletonProducer(string $key, mixed $producer): void;

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
     * @return void
     */
    public function registerInstance(string $key, object $instance): void;

    /**
     * get instance.
     * @param string $key
     * @return mixed
     */
    public function getInstance(string $key): mixed;

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
     * @return void
     */
    public function registerConfigs(array $configs): void;

    /**
     * get all configs.
     * @return array
     */
    public function getConfigs(): array;

    /**
     * register config.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function registerConfig(string $key, mixed $value): void;

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
}
