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

class PhpSerialize extends AbstractAdapter
{
    /**
     * Serialized boolean false value
     *
     * @var null|string
     */
    private static $serializedFalse = null;

    /**
     * Constructor
     */
    public function __construct($options = null)
    {
        // needed to check if a returned false is based on a serialize false
        // or based on failure (igbinary can overwrite [un]serialize functions)
        if (static::$serializedFalse === null) {
            static::$serializedFalse = serialize(false);
        }

        parent::__construct($options);
    }

    /**
     * Serialize using serialize()
     *
     * @param  mixed $value
     * @return string
     * @throws Exception\RuntimeException On serialize error
     */
    public function serialize($value)
    {
        ErrorHandler::start();
        $ret = serialize($value);
        $err = ErrorHandler::stop();
        if ($err) {
            throw new Exception\RuntimeException('Serialization failed', 0, $err);
        }

        return $ret;
    }

    /**
     * Unserialize
     *
     * @todo   Allow integration with unserialize_callback_func
     * @param  string $serialized
     * @return mixed
     * @throws Exception\RuntimeException on unserialize error
     */
    public function unserialize($serialized)
    {
        if (!is_string($serialized) || !preg_match('/^((s|i|d|b|a|O|C):|N;)/', $serialized)) {
            return $serialized;
        }

        // If we have a serialized boolean false value, just return false;
        // prevents the unserialize handler from creating an error.
        if ($serialized === static::$serializedFalse) {
            return false;
        }

        ErrorHandler::start(E_NOTICE);
        $ret = unserialize($serialized);
        $err = ErrorHandler::stop();
        if ($ret === false) {
            throw new Exception\RuntimeException('Unserialization failed', 0, $err);
        }

        return $ret;
    }
}
