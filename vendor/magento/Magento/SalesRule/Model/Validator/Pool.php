<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\SalesRule\Model\Validator;

/**
 * Class Pool collects custom validators for items before SalesRules are applied
 */
class Pool
{
    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @param array $validators
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
     */
    public function getValidators($type)
    {
        return isset($this->validators[$type]) ? $this->validators[$type] : [];
    }
}
