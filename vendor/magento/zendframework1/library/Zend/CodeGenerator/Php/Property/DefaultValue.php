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
 * @see Zend_CodeGenerator_Php_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Property_DefaultValue extends Zend_CodeGenerator_Php_Abstract
{
    /**#@+
     * Constant values
     */
    const TYPE_AUTO     = 'auto';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_BOOL     = 'bool';
    const TYPE_NUMBER   = 'number';
    const TYPE_INTEGER  = 'integer';
    const TYPE_INT      = 'int';
    const TYPE_FLOAT    = 'float';
    const TYPE_DOUBLE   = 'double';
    const TYPE_STRING   = 'string';
    const TYPE_ARRAY    = 'array';
    const TYPE_CONSTANT = 'constant';
    const TYPE_NULL     = 'null';
    const TYPE_OTHER    = 'other';
    /**#@-*/

    /**
     * @var array of reflected constants
     */
    protected static $_constants = array();

    /**
     * @var mixed
     */
    protected $_value = null;

    /**
     * @var string
     */
    protected $_type  = self::TYPE_AUTO;

    /**
     * @var int
     */
    protected $_arrayDepth = 1;

    /**
     * _init()
     *
     * This method will prepare the constant array for this class
     */
    protected function _init()
    {
        if(count(self::$_constants) == 0) {
            $reflect = new ReflectionClass(get_class($this));
            self::$_constants = $reflect->getConstants();
            unset($reflect);
        }
    }

    /**
     * isValidConstantType()
     *
     * @return bool
     */
    public function isValidConstantType()
    {
        if ($this->_type == self::TYPE_AUTO) {
            $type = $this->_getAutoDeterminedType($this->_value);
        } else {
            $type = $this->_type;
        }

        // valid types for constants
        $scalarTypes = array(
            self::TYPE_BOOLEAN,
            self::TYPE_BOOL,
            self::TYPE_NUMBER,
            self::TYPE_INTEGER,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_DOUBLE,
            self::TYPE_STRING,
            self::TYPE_CONSTANT,
            self::TYPE_NULL
            );

        return in_array($type, $scalarTypes);
    }

    /**
     * setValue()
     *
     * @param mixed $value
     * @return Zend_CodeGenerator_Php_Property_DefaultValue
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * getValue()
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * setType()
     *
     * @param string $type
     * @return Zend_CodeGenerator_Php_Property_DefaultValue
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * getType()
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * setArrayDepth()
     *
     * @param int $arrayDepth
     * @return Zend_CodeGenerator_Php_Property_DefaultValue
     */
    public function setArrayDepth($arrayDepth)
    {
        $this->_arrayDepth = $arrayDepth;
        return $this;
    }

    /**
     * getArrayDepth()
     *
     * @return int
     */
    public function getArrayDepth()
    {
        return $this->_arrayDepth;
    }

    /**
     * _getValidatedType()
     *
     * @param string $type
     * @return string
     */
    protected function _getValidatedType($type)
    {
        if (($constName = array_search($type, self::$_constants)) !== false) {
            return $type;
        }

        return self::TYPE_AUTO;
    }

    /**
     * _getAutoDeterminedType()
     *
     * @param mixed $value
     * @return string
     */
    public function _getAutoDeterminedType($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return self::TYPE_BOOLEAN;
            case 'integer':
                return self::TYPE_INT;
            case 'string':
                return self::TYPE_STRING;
            case 'double':
            case 'float':
            case 'integer':
                return self::TYPE_NUMBER;
            case 'array':
                return self::TYPE_ARRAY;
            case 'NULL':
                return self::TYPE_NULL;
            case 'object':
            case 'resource':
            case 'unknown type':
            default:
                return self::TYPE_OTHER;
        }
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $type = $this->_type;

        if ($type != self::TYPE_AUTO) {
            $type = $this->_getValidatedType($type);
        }

        $value = $this->_value;

        if ($type == self::TYPE_AUTO) {
            $type = $this->_getAutoDeterminedType($value);

            if ($type == self::TYPE_ARRAY) {
                $rii = new RecursiveIteratorIterator(
                    $it = new RecursiveArrayIterator($value),
                    RecursiveIteratorIterator::SELF_FIRST
                    );
                foreach ($rii as $curKey => $curValue) {
                    if (!$curValue instanceof Zend_CodeGenerator_Php_Property_DefaultValue) {
                        $curValue = new self(array('value' => $curValue));
                        $rii->getSubIterator()->offsetSet($curKey, $curValue);
                    }
                    $curValue->setArrayDepth($rii->getDepth());
                }
                $value = $rii->getSubIterator()->getArrayCopy();
            }

        }

        $output = '';

        switch ($type) {
            case self::TYPE_BOOLEAN:
            case self::TYPE_BOOL:
                $output .= ( $value ? 'true' : 'false' );
                break;
            case self::TYPE_STRING:
                $output .= "'" . addcslashes($value, "'") . "'";
                break;
            case self::TYPE_NULL:
                $output .= 'null';
                break;
            case self::TYPE_NUMBER:
            case self::TYPE_INTEGER:
            case self::TYPE_INT:
            case self::TYPE_FLOAT:
            case self::TYPE_DOUBLE:
            case self::TYPE_CONSTANT:
                $output .= $value;
                break;
            case self::TYPE_ARRAY:
                $output .= 'array(';
                $curArrayMultiblock = false;
                if (count($value) > 1) {
                    $curArrayMultiblock = true;
                    $output .= PHP_EOL . str_repeat($this->_indentation, $this->_arrayDepth+1);
                }
                $outputParts = array();
                $noKeyIndex = 0;
                foreach ($value as $n => $v) {
                    $v->setArrayDepth($this->_arrayDepth + 1);
                    $partV = $v->generate();
                    $partV = substr($partV, 0, strlen($partV)-1);
                    if ($n === $noKeyIndex) {
                        $outputParts[] = $partV;
                        $noKeyIndex++;
                    } else {
                        $outputParts[] = (is_int($n) ? $n : "'" . addcslashes($n, "'") . "'") . ' => ' . $partV;
                    }

                }
                $output .= implode(',' . PHP_EOL . str_repeat($this->_indentation, $this->_arrayDepth+1), $outputParts);
                if ($curArrayMultiblock == true) {
                    $output .= PHP_EOL . str_repeat($this->_indentation, $this->_arrayDepth+1);
                }
                $output .= ')';
                break;
            case self::TYPE_OTHER:
            default:
                #require_once "Zend/CodeGenerator/Php/Exception.php";
                throw new Zend_CodeGenerator_Php_Exception(
                    "Type '".get_class($value)."' is unknown or cannot be used as property default value."
                );
        }

        $output .= ';';

        return $output;
    }
}
