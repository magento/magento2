<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

interface SymmetricInterface
{
    /**
     * @param string $data
     */
    public function encrypt($data);

    /**
     * @param string $data
     */
    public function decrypt($data);

    /**
     * @param string $key
     */
    public function setKey($key);

    public function getKey();

    public function getKeySize();

    public function getAlgorithm();

    /**
     * @param  string $algo
     */
    public function setAlgorithm($algo);

    public function getSupportedAlgorithms();

    /**
     * @param string|false $salt
     */
    public function setSalt($salt);

    public function getSalt();

    public function getSaltSize();

    public function getBlockSize();

    /**
     * @param string $mode
     */
    public function setMode($mode);

    public function getMode();

    public function getSupportedModes();
}
