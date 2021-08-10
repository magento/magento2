<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\Exception;

use RuntimeException;

/**
 * Exception thrown when SOAP PHP extension is not loaded
 */
class ExtensionNotLoadedException extends RuntimeException
{
}
