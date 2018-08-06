<?php
/**
 * Validator Constraint Option interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Constraint;

/**
 * Interface \Magento\Framework\Validator\Constraint\OptionInterface
 *
 */
interface OptionInterface
{
    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue();
}
