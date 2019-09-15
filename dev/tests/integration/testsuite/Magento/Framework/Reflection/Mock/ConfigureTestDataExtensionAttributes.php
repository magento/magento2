<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Mock;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\TestFramework\Helper\Bootstrap;

class ConfigureTestDataExtensionAttributes
{
    /**
     * Configure extension attributes for TestDataInterface
     */
    public static function execute(): void
    {
        /** @var Config $config */
        $config = Bootstrap::getObjectManager()->get(Config::class);
        $config->merge([TestDataInterface::class => [
            'attribute1' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute2' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute3' => [
                'type' => TestDataObjectSub::class . '[]',
                'resourceRefs' => '',
                'join' => null
            ]
        ]]);
    }
}
