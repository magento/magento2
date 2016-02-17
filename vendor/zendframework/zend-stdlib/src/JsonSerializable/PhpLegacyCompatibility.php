<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\JsonSerializable;

/**
 * Interface compatible with the built-in JsonSerializable interface
 *
 * JsonSerializable was introduced in PHP 5.4.0.
 *
 * @see http://php.net/manual/class.jsonserializable.php
 */
interface PhpLegacyCompatibility
{
    /**
     * Returns data which can be serialized by json_encode().
     *
     * @return mixed
     * @see    http://php.net/manual/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize();
}
