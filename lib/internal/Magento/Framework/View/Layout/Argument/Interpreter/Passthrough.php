<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that pass through params
 * @since 2.0.0
 */
class Passthrough implements InterpreterInterface
{
    /**
     * {@inheritdoc}
     * @return array
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        return $data;
    }
}
