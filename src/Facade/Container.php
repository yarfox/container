<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 8:33 下午
 */

namespace Yarfox\Container\Facade;

use Yarfox\Container\Constant\Constant;
use Yarfox\Container\Contract\Facade;
use Yarfox\Container\Container as RealContainer;

/**
 * @method static void registerProducer(string $key, mixed $producer, string $scope = Constant::SCOPE_PROTOTYPE)
 * @method static void registerSingletonProducer(string $key, mixed $producer)
 * @method static mixed getProducer(string $key)
 * @method static void registerInstance(string $key, object $instance)
 * @method static mixed getInstance(string $key, bool $throwException = false)
 * @method static void reset()
 * @method static mixed resolve(string $key)
 * @method static mixed resolveClass(string $class)
 * @method static void registerConfigs(array $configs)
 * @method static array getConfigs()
 * @method static void registerConfig(string $key, mixed $value)
 * @method static mixed getConfig(string $key, mixed $default = null)
 * @method static get(string $id)
 * @method static bool has(string $id)
 * @see \Yarfox\Container\Container
 */
class Container extends Facade
{
    /**
     * @return RealContainer
     */
    protected static function getProcessor(): RealContainer
    {
        return RealContainer::instance();
    }
}
