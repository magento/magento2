<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument;

/**
 * Interface that encapsulates complexity of expression computation
 *
 * @api
 * @since 2.0.0
 */
interface InterpreterInterface
{
    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function evaluate(array $data);
}
