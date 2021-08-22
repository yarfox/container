<?php

/**
 * This file is part of container.
 *
 * @author      anhoder <anhoderai@xiaoman.cn>
 * @created_at  2021/8/22 4:12 下午
 */

namespace Anhoder\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{

}
