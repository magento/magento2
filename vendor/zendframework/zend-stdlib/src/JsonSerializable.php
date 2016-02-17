<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib;

if (PHP_VERSION_ID < 50400) {
    class_alias(
        'Zend\Stdlib\JsonSerializable\PhpLegacyCompatibility',
        'JsonSerializable'
    );
}

/**
 * Polyfill for JsonSerializable
 *
 * JsonSerializable was introduced in PHP 5.4.0.
 *
 * @see http://php.net/manual/class.jsonserializable.php
 */
interface JsonSerializable extends \JsonSerializable
{
}
