<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class Mage_Backend_Model_Widget_Grid_TotalsAbstract implements Mage_Backend_Model_Widget_Grid_TotalsInterface
{
    /**
     * List of columns should be proceed with expression
     * 'key' => column index
     * 'value' => column expression
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Array of totals based on columns index
     * 'key' => column index
     * 'value' => counted total
     *
     * @var array
     */
    protected $_totals = array();

    /**
     * Factory model
     *
     * @var Varien_Object_Factory
     */
    protected $_factory;

    /**
     * Parser for expressions like operand operation operand
     *
     * @var Mage_Backend_Model_Widget_Grid_Parser
     */
    protected $_parser;

    /**
     * @param Varien_Object_Factory $factory
     * @param Mage_Backend_Model_Widget_Grid_Parser $parser
     */
    public function __construct(Varien_Object_Factory $factory, Mage_Backend_Model_Widget_Grid_Parser $parser)
    {
        $this->_factory = $factory;
        $this->_parser = $parser;
    }

    /**
     * Count collection column sum based on column index
     *
     * @abstract
     * @param string $index
     * @param Varien_Data_Collection $collection
     * @return float|int
     */
    abstract protected function _countSum($index, $collection);

    /**
     * Count collection column average based on column index
     *
     * @abstract
     * @param string $index
     * @param Varien_Data_Collection $collection
     * @return float|int
     */
    abstract protected function _countAverage($index, $collection);

    /**
     * Count collection column sum based on column index and expression
     *
     * @param string $index
     * @param string $expr
     * @param Varien_Data_Collection $collection
     * @return float|int
     */
    protected function _count($index, $expr, $collection)
    {
        switch ($expr) {
            case 'sum':
                $result = $this->_countSum($index, $collection);
                break;
            case 'avg':
                $result = $this->_countAverage($index, $collection);
                break;
            default:
                $result = $this->_countExpr($expr, $collection);
                break;
        }
        $this->_totals[$index] = $result;

        return $result;
    }

    /**
     * Return counted expression accorded parsed string
     *
     * @param string $expr
     * @param Varien_Data_Collection $collection
     * @return float|int
     */
    protected function _countExpr($expr, $collection)
    {
        $parsedExpression = $this->_parser->parseExpression($expr);
        $result = $tmpResult = 0;
        $firstOperand = $secondOperand = null;
        foreach ($parsedExpression as $operand) {
            if ($this->_parser->isOperation($operand)) {
                $this->_checkOperandsSet($firstOperand, $secondOperand, $tmpResult, $result);
                $result = $this->_operate($firstOperand, $secondOperand, $operand, $tmpResult, $result);
                $firstOperand = $secondOperand = null;
            } else {
                if (null === $firstOperand) {
                    $firstOperand = $this->_checkOperand($operand, $collection);
                } elseif (null === $secondOperand) {
                    $secondOperand = $this->_checkOperand($operand, $collection);
                }
            }
        }
        return $result;
    }

    /**
     * Check if operands in not null and set operands values if they are empty
     *
     * @param float|int $firstOperand
     * @param float|int $secondOperand
     * @param float|int $tmpResult
     * @param float|int $result
     */
    protected function _checkOperandsSet(&$firstOperand, &$secondOperand, &$tmpResult, $result)
    {
        if (null === $firstOperand && null === $secondOperand) {
            $firstOperand = $tmpResult;
            $secondOperand = $result;
        } elseif (null !== $firstOperand && null === $secondOperand) {
            $secondOperand = $result;
        } elseif (null !== $firstOperand && null !== $secondOperand) {
            $tmpResult = $result;
        }
    }

    /**
     * Get result of operation
     *
     * @param float|int $firstOperand
     * @param float|int $secondOperand
     * @param string $operation
     * @return float|int
     */
    protected function _operate($firstOperand, $secondOperand, $operation)
    {
        $result = 0;
        switch ($operation) {
            case '+':
                $result = $firstOperand + $secondOperand;
                break;
            case '-':
                $result = $firstOperand - $secondOperand;
                break;
            case '*':
                $result = $firstOperand * $secondOperand;
                break;
            case '/':
                $result = ($secondOperand) ? $firstOperand / $secondOperand : $secondOperand;
                break;
        }
        return $result;
    }

    /**
     * Check operand is numeric or has already counted
     *
     * @param string $operand
     * @param Varien_Data_Collection $collection
     * @return float|int
     */
    protected function _checkOperand($operand, $collection)
    {
        if (!is_numeric($operand)) {
            if (isset($this->_totals[$operand])) {
                $operand = $this->_totals[$operand];
            } else {
                $operand = $this->_count($operand, $this->_columns[$operand], $collection);
            }
        } else {
            $operand *= 1;
        }
        return $operand;
    }

    /**
     * Fill columns
     *
     * @param string $index
     * @param string $totalExpr
     * @return Mage_Backend_Model_Widget_Grid_TotalsAbstract
     */
    public function setColumn($index, $totalExpr)
    {
        $this->_columns[$index] = $totalExpr;
        return $this;
    }

    /**
     * Return columns set
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Count totals for all columns set
     *
     * @param Varien_Data_Collection $collection
     * @return Varien_Object
     */
    public function countTotals($collection)
    {
        foreach ($this->_columns as $index => $expr) {
            $this->_count($index, $expr, $collection);
        }

        return $this->getTotals();
    }

    /**
     * Get totals as object
     *
     * @return Varien_Object
     */
    public function getTotals()
    {
        return $this->_factory->create($this->_totals);
    }


    /**
     * Reset totals and columns set
     *
     * @param bool $isFullReset
     */
    public function reset($isFullReset = false)
    {
        if ($isFullReset) {
            $this->_columns = array();
        }

        $this->_totals = array();
    }
}
