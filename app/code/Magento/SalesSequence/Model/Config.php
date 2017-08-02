<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

/**
 * Class Config - configuration container for sequence
 *
 * @api
 * @since 2.0.0
 */
class Config
{
    /**
     * Default sequence values
     * Prefix represents prefix for sequence: AA000
     * Suffix represents suffix: 000AA
     * startValue represents initial value
     * warning value will be using for alert messages when increment closing to overflow
     * maxValue represents last available increment id in system
     *
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($key = null)
    {
        if (!array_key_exists($key, $this->defaultValues)) {
            return null;
        }
        return $this->defaultValues[$key];
    }
}
