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
 * @see Zend_CodeGenerator_Php_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Docblock.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_CodeGenerator_Php_Member_Abstract extends Zend_CodeGenerator_Php_Abstract
{

    /**#@+
     * @param const string
     */
    const VISIBILITY_PUBLIC    = 'public';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE   = 'private';
    /**#@-*/

    /**
     * @var Zend_CodeGenerator_Php_Docblock
     */
    protected $_docblock   = null;

    /**
     * @var bool
     */
    protected $_isAbstract = false;

    /**
     * @var bool
     */
    protected $_isFinal    = false;

    /**
     * @var bool
     */
    protected $_isStatic   = false;

    /**
     * @var const
     */
    protected $_visibility = self::VISIBILITY_PUBLIC;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * setDocblock() Set the docblock
     *
     * @param Zend_CodeGenerator_Php_Docblock|array|string $docblock
     * @return Zend_CodeGenerator_Php_File
     */
    public function setDocblock($docblock)
    {
        if (is_string($docblock)) {
            $docblock = array('shortDescription' => $docblock);
        }

        if (is_array($docblock)) {
            $docblock = new Zend_CodeGenerator_Php_Docblock($docblock);
        } elseif (!$docblock instanceof Zend_CodeGenerator_Php_Docblock) {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('setDocblock() is expecting either a string, array or an instance of Zend_CodeGenerator_Php_Docblock');
        }

        $this->_docblock = $docblock;
        return $this;
    }

    /**
     * getDocblock()
     *
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function getDocblock()
    {
        return $this->_docblock;
    }

    /**
     * setAbstract()
     *
     * @param bool $isAbstract
     * @return Zend_CodeGenerator_Php_Member_Abstract
     */
    public function setAbstract($isAbstract)
    {
        $this->_isAbstract = ($isAbstract) ? true : false;
        return $this;
    }

    /**
     * isAbstract()
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->_isAbstract;
    }

    /**
     * setFinal()
     *
     * @param bool $isFinal
     * @return Zend_CodeGenerator_Php_Member_Abstract
     */
    public function setFinal($isFinal)
    {
        $this->_isFinal = ($isFinal) ? true : false;
        return $this;
    }

    /**
     * isFinal()
     *
     * @return bool
     */
    public function isFinal()
    {
        return $this->_isFinal;
    }

    /**
     * setStatic()
     *
     * @param bool $isStatic
     * @return Zend_CodeGenerator_Php_Member_Abstract
     */
    public function setStatic($isStatic)
    {
        $this->_isStatic = ($isStatic) ? true : false;
        return $this;
    }

    /**
     * isStatic()
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->_isStatic;
    }

    /**
     * setVisitibility()
     *
     * @param const $visibility
     * @return Zend_CodeGenerator_Php_Member_Abstract
     */
    public function setVisibility($visibility)
    {
        $this->_visibility = $visibility;
        return $this;
    }

    /**
     * getVisibility()
     *
     * @return const
     */
    public function getVisibility()
    {
        return $this->_visibility;
    }

    /**
     * setName()
     *
     * @param string $name
     * @return Zend_CodeGenerator_Php_Member_Abstract
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
