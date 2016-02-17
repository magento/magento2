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

class PythonPickleOptions extends AdapterOptions
{
    /**
     * Pickle protocol version to serialize data
     *
     * @var int
     */
    protected $protocol = 0;

    /**
     * Set pickle protocol version to serialize data
     *
     * Supported versions are 0, 1, 2 and 3
     *
     * @param  int $protocol
     * @return PythonPickleOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setProtocol($protocol)
    {
        $protocol = (int) $protocol;
        if ($protocol < 0 || $protocol > 3) {
            throw new Exception\InvalidArgumentException(
                "Invalid or unknown protocol version '{$protocol}'"
            );
        }

        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get pickle protocol version to serialize data
     *
     * @return int
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
