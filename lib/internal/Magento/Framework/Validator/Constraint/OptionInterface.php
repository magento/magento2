<?php
/**
 * Validator Constraint Option interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Constraint;

interface OptionInterface
{
    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue();
}
