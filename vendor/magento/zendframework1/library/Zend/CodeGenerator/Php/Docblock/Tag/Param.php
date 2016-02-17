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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag
 */
#require_once 'Zend/CodeGenerator/Php/Docblock/Tag.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Docblock_Tag_Param extends Zend_CodeGenerator_Php_Docblock_Tag
{

    /**
     * @var string
     */
    protected $_datatype = null;

    /**
     * @var string
     */
    protected $_paramName = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagParam
     * @return Zend_CodeGenerator_Php_Docblock_Tag
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagParam)
    {
        $paramTag = new self();

        $paramTag->setName('param');
        $paramTag->setDatatype($reflectionTagParam->getType()); // @todo rename
        $paramTag->setParamName($reflectionTagParam->getVariableName());
        $paramTag->setDescription($reflectionTagParam->getDescription());

        return $paramTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Param
     */
    public function setDatatype($datatype)
    {
        $this->_datatype = $datatype;
        return $this;
    }

    /**
     * getDatatype
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }

    /**
     * setParamName()
     *
     * @param string $paramName
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Param
     */
    public function setParamName($paramName)
    {
        $this->_paramName = $paramName;
        return $this;
    }

    /**
     * getParamName()
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->_paramName;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@param '
            . (($this->_datatype  != null) ? $this->_datatype : 'unknown')
            . (($this->_paramName != null) ? ' $' . $this->_paramName : '')
            . (($this->_description != null) ? ' ' . $this->_description : '');
        return $output;
    }

}
