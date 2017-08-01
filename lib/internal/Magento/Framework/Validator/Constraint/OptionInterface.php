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
 * @since 2.0.0
 */
interface OptionInterface
{
    /**
     * Get option value
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue();
}
