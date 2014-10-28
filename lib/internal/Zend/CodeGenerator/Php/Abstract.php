<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Abstract
 */
#require_once 'Zend/CodeGenerator/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_CodeGenerator_Php_Abstract extends Zend_CodeGenerator_Abstract
{

    /**
     * Line feed to use in place of EOL
     *
     */
    const LINE_FEED = "\n";

    /**
     * @var bool
     */
    protected $_isSourceDirty = true;

    /**
     * @var int|string
     */
    protected $_indentation = '    ';

    /**
     * setSourceDirty()
     *
     * @param bool $isSourceDirty
     * @return Zend_CodeGenerator_Php_Abstract
     */
    public function setSourceDirty($isSourceDirty = true)
    {
        $this->_isSourceDirty = ($isSourceDirty) ? true : false;
        return $this;
    }

    /**
     * isSourceDirty()
     *
     * @return bool
     */
    public function isSourceDirty()
    {
        return $this->_isSourceDirty;
    }

    /**
     * setIndentation()
     *
     * @param string|int $indentation
     * @return Zend_CodeGenerator_Php_Abstract
     */
    public function setIndentation($indentation)
    {
        $this->_indentation = $indentation;
        return $this;
    }

    /**
     * getIndentation()
     *
     * @return string|int
     */
    public function getIndentation()
    {
        return $this->_indentation;
    }

}
