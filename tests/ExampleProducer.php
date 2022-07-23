<?php
/**
 * The file is part of the container.
 *
 * (c) anhoder <anhoder@88.com>.
 *
 * 2022/7/23 18:22
 */

namespace Yarfox\Container\Test;

use Yarfox\Container\Contract\ProducerInterface;

class ExampleProducer implements ProducerInterface
{

    /**
     * @inheritDoc
     */
    public function produce(): object
    {
        return new class {
            public function test(): string
            {
                return 'test';
            }
        };
    }
}
