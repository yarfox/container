<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:13 下午
 */

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}
