<?php
/**
 * This file is part of Jrean\SuperCollege package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\SuperCollege\Facades;

use Illuminate\Support\Facades\Facade;

class SuperCollege extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'supercollege';
    }
}
