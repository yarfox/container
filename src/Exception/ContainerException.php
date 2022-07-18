<?php

/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:12 下午
 */

namespace Yarfox\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{

}
