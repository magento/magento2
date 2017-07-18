<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid;

/**
 * @api
 */
class Parser
{
    /**
     * List of allowed operations
     *
     * @var string[]
     */
    protected $_operations = ['-', '+', '/', '*'];

    /**
     * Parse expression
     *
     * @param string $expression
     * @return array
     */
    public function parseExpression($expression)
    {
        $stack = [];
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
        return empty($stack) ? [$expression] : $stack;
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
