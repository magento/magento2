<?php
/**
 * Validator Constraint Option interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
