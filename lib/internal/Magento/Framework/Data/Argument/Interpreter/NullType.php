<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter of NULL data type
 */
class NullType implements InterpreterInterface
{
    /**
     * {@inheritdoc}
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function evaluate(array $data)
    {
        return null;
    }
}
