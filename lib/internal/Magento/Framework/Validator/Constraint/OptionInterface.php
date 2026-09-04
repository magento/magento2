<?php
/**
 * Validator Constraint Option interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Validator\Constraint;

/**
 * Interface \Magento\Framework\Validator\Constraint\OptionInterface
 *
 * @api
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
