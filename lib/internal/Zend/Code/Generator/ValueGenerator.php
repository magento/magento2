<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

class ValueGenerator extends AbstractGenerator
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
    const TYPE_OBJECT   = 'object';
    const TYPE_OTHER    = 'other';
    /**#@-*/

    const OUTPUT_MULTIPLE_LINE = 'multipleLine';
    const OUTPUT_SINGLE_LINE   = 'singleLine';

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var string
     */
    protected $type = self::TYPE_AUTO;

    /**
     * @var int
     */
    protected $arrayDepth = 1;

    /**
     * @var string
     */
    protected $outputMode = self::OUTPUT_MULTIPLE_LINE;

    /**
     * @var array
     */
    protected $allowedTypes = null;

    public function __construct($value = null, $type = self::TYPE_AUTO, $outputMode = self::OUTPUT_MULTIPLE_LINE)
    {
        if ($value !== null) { // strict check is important here if $type = AUTO
            $this->setValue($value);
        }
        if ($type !== self::TYPE_AUTO) {
            $this->setType($type);
        }
        if ($outputMode !== self::OUTPUT_MULTIPLE_LINE) {
            $this->setOutputMode($outputMode);
        }

    }

    /**
     * isValidConstantType()
     *
     * @return bool
     */
    public function isValidConstantType()
    {
        if ($this->type == self::TYPE_AUTO) {
            $type = $this->getAutoDeterminedType($this->value);
        } else {
            $type = $this->type;
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
     * @return ValueGenerator
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * getValue()
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * setType()
     *
     * @param string $type
     * @return ValueGenerator
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * getType()
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * setArrayDepth()
     *
     * @param int $arrayDepth
     * @return ValueGenerator
     */
    public function setArrayDepth($arrayDepth)
    {
        $this->arrayDepth = $arrayDepth;
        return $this;
    }

    /**
     * getArrayDepth()
     *
     * @return int
     */
    public function getArrayDepth()
    {
        return $this->arrayDepth;
    }

    /**
     * _getValidatedType()
     *
     * @param string $type
     * @return string
     */
    protected function getValidatedType($type)
    {
        $types = array(
            self::TYPE_AUTO,
            self::TYPE_BOOLEAN,
            self::TYPE_BOOL,
            self::TYPE_NUMBER,
            self::TYPE_INTEGER,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_DOUBLE,
            self::TYPE_STRING,
            self::TYPE_ARRAY,
            self::TYPE_CONSTANT,
            self::TYPE_NULL,
            self::TYPE_OBJECT,
            self::TYPE_OTHER
        );

        if (in_array($type, $types)) {
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
    public function getAutoDeterminedType($value)
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
     * @throws Exception\RuntimeException
     * @return string
     */
    public function generate()
    {
        $type = $this->type;

        if ($type != self::TYPE_AUTO) {
            $type = $this->getValidatedType($type);
        }

        $value = $this->value;

        if ($type == self::TYPE_AUTO) {
            $type = $this->getAutoDeterminedType($value);

            if ($type == self::TYPE_ARRAY) {
                $rii = new \RecursiveIteratorIterator(
                    $it = new \RecursiveArrayIterator($value),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($rii as $curKey => $curValue) {
                    if (!$curValue instanceof ValueGenerator) {
                        $curValue = new self($curValue);
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
                $output .= ($value ? 'true' : 'false');
                break;
            case self::TYPE_STRING:
                $output .= self::escape($value);
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
                    if ($this->outputMode == self::OUTPUT_MULTIPLE_LINE) {
                        $output .= self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth + 1);
                    }
                }
                $outputParts = array();
                $noKeyIndex  = 0;
                foreach ($value as $n => $v) {
                    /* @var $v ValueGenerator */
                    $v->setArrayDepth($this->arrayDepth + 1);
                    $partV = $v->generate();
                    if ($n === $noKeyIndex) {
                        $outputParts[] = $partV;
                        $noKeyIndex++;
                    } else {
                        $outputParts[] = (is_int($n) ? $n : self::escape($n)) . ' => ' . $partV;
                    }
                }
                $padding = ($this->outputMode == self::OUTPUT_MULTIPLE_LINE)
                    ? self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth + 1)
                    : ' ';
                $output .= implode(',' . $padding, $outputParts);
                if ($curArrayMultiblock == true && $this->outputMode == self::OUTPUT_MULTIPLE_LINE) {
                    $output .= self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth + 1);
                }
                $output .= ')';
                break;
            case self::TYPE_OTHER:
            default:
                throw new Exception\RuntimeException(
                    "Type '" . get_class($value) . "' is unknown or cannot be used as property default value."
                );
        }

        return $output;
    }

    /**
     * Quotes value for PHP code.
     *
     * @param string $input Raw string.
     * @param bool   $quote Whether add surrounding quotes or not.
     * @return string PHP-ready code.
     */
    public static function escape($input, $quote = true)
    {
        $output = addcslashes($input, "'");

        // adds quoting strings
        if ($quote) {
            $output = "'" . $output . "'";
        }

        return $output;
    }

    /**
     * @param string $outputMode
     * @return ValueGenerator
     */
    public function setOutputMode($outputMode)
    {
        $this->outputMode = $outputMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputMode()
    {
        return $this->outputMode;
    }

    public function __toString()
    {
        return $this->generate();
    }

}
