<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\App\Mode;

use Magento\Deploy\App\Mode\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testGetConfigs()
    {
        $expectedValue = [
            '{{setting_path}}' => '{{setting_value}}'
        ];
        $configProvider = new ConfigProvider(
            [
                'developer' => [
                    'production' => $expectedValue
                ]
            ]
        );
        $this->assertEquals($expectedValue, $configProvider->getConfigs('developer', 'production'));
        $this->assertEquals([], $configProvider->getConfigs('undefined', 'production'));
    }
}
