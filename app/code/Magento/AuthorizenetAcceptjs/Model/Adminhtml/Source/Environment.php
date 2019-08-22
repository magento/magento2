<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\Adminhtml\Source;

/**
 * Authorize.net Environment Dropdown source
 */
class Environment implements \Magento\Framework\Data\OptionSourceInterface
{
    const ENVIRONMENT_PRODUCTION = 'production';
    const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray(): array
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
