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
 * @version    $Id: Method.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Php_Member_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Member/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock
 */
#require_once 'Zend/CodeGenerator/Php/Docblock.php';

/**
 * @see Zend_CodeGenerator_Php_Parameter
 */
#require_once 'Zend/CodeGenerator/Php/Parameter.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Method extends Zend_CodeGenerator_Php_Member_Abstract
{
    /**
     * @var Zend_CodeGenerator_Php_Docblock
     */
    protected $_docblock = null;

    /**
     * @var bool
     */
    protected $_isFinal = false;

    /**
     * @var array
     */
    protected $_parameters = array();

    /**
     * @var string
     */
    protected $_body = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Method $reflectionMethod
     * @return Zend_CodeGenerator_Php_Method
     */
    public static function fromReflection(Zend_Reflection_Method $reflectionMethod)
    {
        $method = new self();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocblock(Zend_CodeGenerator_Php_Docblock::fromReflection($reflectionMethod->getDocblock()));
        }

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $method->setStatic($reflectionMethod->isStatic());

        $method->setName($reflectionMethod->getName());

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(Zend_CodeGenerator_Php_Parameter::fromReflection($reflectionParameter));
        }

        $method->setBody($reflectionMethod->getBody());

        return $method;
    }

    /**
     * setFinal()
     *
     * @param bool $isFinal
     */
    public function setFinal($isFinal)
    {
        $this->_isFinal = ($isFinal) ? true : false;
    }

    /**
     * setParameters()
     *
     * @param array $parameters
     * @return Zend_CodeGenerator_Php_Method
     */
    public function setParameters(Array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->setParameter($parameter);
        }
        return $this;
    }

    /**
     * setParameter()
     *
     * @param Zend_CodeGenerator_Php_Parameter|array $parameter
     * @return Zend_CodeGenerator_Php_Method
     */
    public function setParameter($parameter)
    {
        if (is_array($parameter)) {
            $parameter = new Zend_CodeGenerator_Php_Parameter($parameter);
            $parameterName = $parameter->getName();
        } elseif ($parameter instanceof Zend_CodeGenerator_Php_Parameter) {
            $parameterName = $parameter->getName();
        } else {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception('setParameter() expects either an array of method options or an instance of Zend_CodeGenerator_Php_Parameter');
        }

        $this->_parameters[$parameterName] = $parameter;
        return $this;
    }

    /**
     * getParameters()
     *
     * @return array Array of Zend_CodeGenerator_Php_Parameter
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * setBody()
     *
     * @param string $body
     * @return Zend_CodeGenerator_Php_Method
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * getBody()
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '';

        $indent = $this->getIndentation();

        if (($docblock = $this->getDocblock()) !== null) {
            $docblock->setIndentation($indent);
            $output .= $docblock->generate();
        }

        $output .= $indent;

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } else {
            $output .= (($this->isFinal()) ? 'final ' : '');
        }

        $output .= $this->getVisibility()
            . (($this->isStatic()) ? ' static' : '')
            . ' function ' . $this->getName() . '(';

        $parameters = $this->getParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterOuput[] = $parameter->generate();
            }

            $output .= implode(', ', $parameterOuput);
        }

        $output .= ')' . self::LINE_FEED . $indent . '{' . self::LINE_FEED;

        if ($this->_body) {
            $output .= '        '
                    .  str_replace(self::LINE_FEED, self::LINE_FEED . $indent . $indent, trim($this->_body))
                    .  self::LINE_FEED;
        }

        $output .= $indent . '}' . self::LINE_FEED;

        return $output;
    }

}
