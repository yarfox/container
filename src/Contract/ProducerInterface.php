<?php
/**
 * The file is part of the container-attributes.
 *
 * (c) anhoder <anhoder@88.com>.
 *
 * 2022/7/23 12:24
 */

namespace Yarfox\Container\Contract;

interface ProducerInterface
{
    /**
     * @return object
     */
    public function produce(): object;
}
