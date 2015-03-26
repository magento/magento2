<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

/**
 * Class Config
 */
class Config
{
    /**
     * Default sequence values
     *
     * @var array
     */
    protected $defaultValues = [
        'prefix' => '',
        'suffix' => '',
        'startValue' => 1,
        'step' => 1,
        'warningValue' => 4294966295,
        'maxValue' => 4294967295
    ];

    /**
     * Get configuration field
     *
     * @param string|null $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (!array_key_exists($key, $this->defaultValues)) {
            return null;
        }
        return $this->defaultValues[$key];
    }
}