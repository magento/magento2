<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument;

/**
 * Interface that encapsulates complexity of expression computation
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
     * @throws MissingOptionalValueException
     */
    public function evaluate(array $data);
}
