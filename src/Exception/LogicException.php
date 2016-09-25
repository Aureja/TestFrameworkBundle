<?php

/*
 * This file is part of the Aureja package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aureja\Bundle\TestFrameworkBundle\Exception;

use LogicException as BaseLogicException;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 6/1/16 10:43 PM
 */
class LogicException extends BaseLogicException
{
    /**
     * @param string $message
     *
     * @return LogicException
     */
    public static function create($message)
    {
        return new self($message);
    }
}
 