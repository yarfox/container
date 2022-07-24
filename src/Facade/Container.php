<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 8:33 下午
 */

namespace Yarfox\Container\Facade;

use Yarfox\Container\Constant\Scope;
use Yarfox\Container\Container as RealContainer;
use Yarfox\Utils\Facade\AbstractFacade;

/**
 * @method static void registerProducer(string $key, mixed $producer, string $scope = Scope::SCOPE_PROTOTYPE)
 * @method static void registerSingletonProducer(string $key, mixed $producer)
 * @method static void registerRequestProducer(string $key, mixed $producer)
 * @method static mixed getProducer(string $key)
 * @method static void registerInstance(string $key, object $instance, string $scope = Scope::SCOPE_GLOBAL)
 * @method static mixed getInstance(string $key, bool $throwException = false)
 * @method static mixed resolve(string $key)
 * @method static mixed resolveClass(string $class)
 * @method static void registerConfigs(array $configs, string $scope = Scope::SCOPE_GLOBAL)
 * @method static array getConfigs()
 * @method static void registerConfig(string $key, mixed $value, string $scope = Scope::SCOPE_GLOBAL)
 * @method static mixed getConfig(string $key, mixed $default = null)
 * @method static void resetAll()
 * @method static void resetRequestScope()
 * @method static get(string $id)
 * @method static bool has(string $id)
 * @method static RealContainer instance();
 * @see \Yarfox\Container\Container
 */
class Container extends AbstractFacade
{
    /**
     * @return RealContainer
     */
    protected static function getProcessor(): RealContainer
    {
        return RealContainer::instance();
    }
}
