<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Source;

/**
 * Class Environment
 * @codeCoverageIgnore
 */
class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const ENVIRONMENT_PRODUCTION    = 'production';
    const ENVIRONMENT_SANDBOX       = 'sandbox';

    /**
     * Possible environment types
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => 'Sandbox',
            ],
            [
                'value' => self::ENVIRONMENT_PRODUCTION,
                'label' => 'Production'
            ]
        ];
    }
}
