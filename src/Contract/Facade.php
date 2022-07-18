<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 8:19 下午
 */

namespace Yarfox\Container\Contract;

use Yarfox\Container\Container;
use RuntimeException;

/**
 * Facade class.
 */
abstract class Facade
{
    /**
     * @var object|null
     */
    private static ?object $processor = null;

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic(string $name, array $args)
    {
        $processor = static::getProcessor();
        if (!static::$processor) {
            static::$processor = is_object($processor) ? $processor : Container::instance()->getInstance($processor);
        }

        if (!static::$processor) {
            throw new RuntimeException("Processor {$processor} not exists!");
        }

        return static::$processor->{$name}(...$args);
    }

    /**
     * @return mixed
     */
    abstract protected static function getProcessor(): mixed;
}
