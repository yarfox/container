<?php
/**
 * This file is part of container.
 *
 * @author      anhoder <anhoder@88.com>
 * @created_at  2021/8/22 4:50 下午
 */

namespace Yarfox\Container\Constant;

class Scope
{
    public const SCOPE_PROTOTYPE = 'PROTOTYPE'; // create every time.
    public const SCOPE_REQUEST   = 'REQUEST';   // request.
    public const SCOPE_GLOBAL    = 'GLOBAL';    // global.

    public const SCOPES = [
        self::SCOPE_PROTOTYPE => 'PROTOTYPE',
        self::SCOPE_REQUEST   => 'REQUEST',
        self::SCOPE_GLOBAL    => 'SCOPE',
    ];

    public const CACHEABLE_SCOPES = [
        self::SCOPE_REQUEST => 'REQUEST',
        self::SCOPE_GLOBAL  => 'SCOPE',
    ];
}
