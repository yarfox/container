<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 8:33 下午
 */

namespace Anhoder\Container\Facade;

use Anhoder\Container\Contract\Facade;
use Anhoder\Container\Container as RealContainer;

/**
 * @mixin \Anhoder\Container\Container
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
