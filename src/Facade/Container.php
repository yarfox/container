<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 8:33 下午
 */

namespace Anhoder\Container\Facade;

use Anhoder\Container\Constant\Constant;
use Anhoder\Container\Contract\Facade;
use Anhoder\Container\Container as RealContainer;

/**
 * @method static registerProducer(string $key, mixed $producer, string $scope = Constant::SCOPE_PROTOTYPE)
 * @method static registerSingletonProducer(string $key, mixed $producer)
 * @method static mixed getProducer(string $key)
 * @method static registerInstance(string $key, object $instance)
 * @method static mixed getInstance(string $key)
 * @method static mixed resolve(string $key)
 * @method static mixed resolveClass(string $class)
 * @method static registerConfigs(array $configs)
 * @method static array getConfigs()
 * @method static registerConfig(string $key, mixed $value)
 * @method static mixed getConfig(string $key, mixed $default = null)
 * @method static get(string $id)
 * @method static bool has(string $id)
 * @see \Anhoder\Container\Container
 */
class Container extends Facade
{
    /**
     * @return mixed
     */
    protected static function getProcessor(): mixed
    {
        return RealContainer::instance();
    }
}
