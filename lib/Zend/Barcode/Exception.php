<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE.txt, and
 * is available through the world-wide-web at the following URL:
 * http://framework.zend.com/license/new-bsd. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 22999 2010-09-23 19:43:14Z mikaelkael $
 */
/**
 * Zend_Exception
 */
#require_once 'Zend/Exception.php';

/**
 * Zend_Barcode_Exception
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Exception extends Zend_Exception
{
    /**
     * Is this exception renderable?
     * @var bool
     */
    protected $_isRenderable = true;

    /**
     * Set renderable flag
     *
     * @param  bool $flag
     * @return Zend_Barcode_Exception
     */
    public function setIsRenderable($flag)
    {
        $this->_isRenderable = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve renderable flag
     *
     * @return bool
     */
    public function isRenderable()
    {
        return $this->_isRenderable;
    }
}
