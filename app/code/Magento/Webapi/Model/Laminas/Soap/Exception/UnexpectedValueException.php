<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\Exception;

use UnexpectedValueException as LaminasSoapUnexpectedValueException;

/**
 * Exception thrown when provided arguments are invalid
 */
class UnexpectedValueException extends LaminasSoapUnexpectedValueException
{
}
