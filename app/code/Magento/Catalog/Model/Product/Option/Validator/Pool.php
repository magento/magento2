<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

/**
 * Class \Magento\Catalog\Model\Product\Option\Validator\Pool
 *
 * @since 2.0.0
 */
class Pool
{
    /**
     * @var \Zend_Validate_Interface
     * @since 2.0.0
     */
    protected $validators;

    /**
     * @param \Zend_Validate_Interface[] $validators
     * @since 2.0.0
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Get validator
     *
     * @param string $type
     * @return \Zend_Validate_Interface
     * @since 2.0.0
     */
    public function get($type)
    {
        return isset($this->validators[$type]) ? $this->validators[$type] : $this->validators['default'];
    }
}
