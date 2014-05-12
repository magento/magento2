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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Widget\Grid;

class Parser
{
    /**
     * List of allowed operations
     *
     * @var string[]
     */
    protected $_operations = array('-', '+', '/', '*');

    /**
     * Parse expression
     *
     * @param string $expression
     * @return array
     */
    public function parseExpression($expression)
    {
        $stack = array();
        $expression = trim($expression);
        foreach ($this->_operations as $operation) {
            $splittedExpr = preg_split('/\\' . $operation . '/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (count($splittedExpr) > 1) {
                for ($i = 0; $i < count($splittedExpr); $i++) {
                    $stack = array_merge($stack, $this->parseExpression($splittedExpr[$i]));
                    if ($i > 0) {
                        $stack[] = $operation;
                    }
                }
                break;
            }
        }
        return empty($stack) ? array($expression) : $stack;
    }

    /**
     * Check if string is operation
     *
     * @param string $operation
     * @return bool
     */
    public function isOperation($operation)
    {
        return in_array($operation, $this->_operations);
    }
}
