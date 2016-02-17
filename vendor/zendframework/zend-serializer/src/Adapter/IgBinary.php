<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

use Zend\Serializer\Exception;
use Zend\Stdlib\ErrorHandler;

class IgBinary extends AbstractAdapter
{
    /**
     * @var string Serialized null value
     */
    private static $serializedNull = null;

    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException If igbinary extension is not present
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('igbinary')) {
            throw new Exception\ExtensionNotLoadedException(
                'PHP extension "igbinary" is required for this adapter'
            );
        }

        if (static::$serializedNull === null) {
            static::$serializedNull = igbinary_serialize(null);
        }

        parent::__construct($options);
    }

    /**
     * Serialize PHP value to igbinary
     *
     * @param  mixed $value
     * @return string
     * @throws Exception\RuntimeException on igbinary error
     */
    public function serialize($value)
    {
        ErrorHandler::start();
        $ret = igbinary_serialize($value);
        $err = ErrorHandler::stop();

        if ($ret === false) {
            throw new Exception\RuntimeException('Serialization failed', 0, $err);
        }

        return $ret;
    }

    /**
     * Deserialize igbinary string to PHP value
     *
     * @param  string $serialized
     * @return mixed
     * @throws Exception\RuntimeException on igbinary error
     */
    public function unserialize($serialized)
    {
        if ($serialized === static::$serializedNull) {
            return;
        }

        ErrorHandler::start();
        $ret = igbinary_unserialize($serialized);
        $err = ErrorHandler::stop();

        if ($ret === null) {
            throw new Exception\RuntimeException('Unserialization failed', 0, $err);
        }

        return $ret;
    }
}
