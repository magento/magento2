<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that pass through params
 */
class Passthrough implements InterpreterInterface
{
    /**
     * {@inheritdoc}
     * @return array
     */
    public function evaluate(array $data)
    {
        return $data;
    }
}
