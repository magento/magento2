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
 * @version    $Id: Return.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag
 */
#require_once 'Zend/CodeGenerator/Php/Docblock/Tag.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Docblock_Tag_Return extends Zend_CodeGenerator_Php_Docblock_Tag
{

    /**
     * @var string
     */
    protected $_datatype = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagReturn
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Return
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagReturn)
    {
        $returnTag = new self();

        $returnTag->setName('return');
        $returnTag->setDatatype($reflectionTagReturn->getType()); // @todo rename
        $returnTag->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Return
     */
    public function setDatatype($datatype)
    {
        $this->_datatype = $datatype;
        return $this;
    }

    /**
     * getDatatype()
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@return ' . $this->_datatype . ' ' . $this->_description;
        return $output;
    }

}