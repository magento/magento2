<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\App\Mode;

use Magento\Deploy\App\Mode\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
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
