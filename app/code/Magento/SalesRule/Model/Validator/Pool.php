<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Validator;

/**
 * Class Pool collects custom validators for items before SalesRules are applied
 * @since 2.0.0
 */
class Pool
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $validators = [];

    /**
     * @param array $validators
     * @since 2.0.0
     */
    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * Get Validators defined in di
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getValidators($type)
    {
        return isset($this->validators[$type]) ? $this->validators[$type] : [];
    }
}
