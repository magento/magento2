<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\PublicKey\Rsa;

abstract class AbstractKey
{
    const DEFAULT_KEY_SIZE = 2048;

    /**
     * PEM formatted key
     *
     * @var string
     */
    protected $pemString = null;

    /**
     * Key Resource
     *
     * @var resource
     */
    protected $opensslKeyResource = null;

    /**
     * Openssl details array
     *
     * @var array
     */
    protected $details = array();

    /**
     * Get key size in bits
     *
     * @return int
     */
    public function getSize()
    {
        return $this->details['bits'];
    }

    /**
     * Retrieve openssl key resource
     *
     * @return resource
     */
    public function getOpensslKeyResource()
    {
        return $this->opensslKeyResource;
    }

    /**
     * Encrypt using this key
     *
     * @abstract
     * @param string $data
     * @return string
     */
    abstract public function encrypt($data);

    /**
     * Decrypt using this key
     *
     * @abstract
     * @param string $data
     * @return string
     */
    abstract public function decrypt($data);

    /**
     * Get string representation of this key
     *
     * @abstract
     * @return string
     */
    abstract public function toString();

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
