<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model\Config\Structure;

use Magento\Variable\Model\Config\Structure\AvailableVariables;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AvailableVariables
 */
class AvailableVariablesTest extends TestCase
{
    /**
     * @covers \Magento\Variable\Model\Config\Structure\AvailableVariables::getConfigPaths
     * @dataProvider getConfigPathsDataProvider
     */
    public function testGetConfigPaths($data, $expected)
    {
        $model = new AvailableVariables($data);
        $this->assertEquals($expected, $model->getConfigPaths());
    }

    /**
     * @return array
     */
    public static function getConfigPathsDataProvider()
    {
        return [
            [[],[]],
            [
                [
                    'web' => [
                        'web/unsecure/base_url' => '1',
                        'web/secure/base_url' => '1'
                    ],
                    'general/store_information' => [
                        'general/store_information/name' => '1',
                        'general/store_information/hours' => '1'
                    ],
                ],
                [
                    'web' => [
                        'web/unsecure/base_url' => '1',
                        'web/secure/base_url' => '1'
                    ],
                    'general/store_information' => [
                        'general/store_information/name' => '1',
                        'general/store_information/hours' => '1'
                    ],
                ]
            ],
        ];
    }

    /**
     * @covers \Magento\Variable\Model\Config\Structure\AvailableVariables::getFlatConfigPaths
     */
    public function testGetFlatConfigPaths()
    {
        $configVariables = [
            'web' => [
                'web/unsecure/base_url' => '1',
                'web/secure/base_url' => '1'
            ],
            'general/store_information' => [
                'general/store_information/name' => '1',
                'general/store_information/hours' => '1'
            ],
        ];
        $expected = [
            'web/unsecure/base_url' => '1',
            'web/secure/base_url' => '1',
            'general/store_information/name' => '1',
            'general/store_information/hours' => '1'
        ];
        $model = new AvailableVariables($configVariables);
        $this->assertEquals($expected, $model->getFlatConfigPaths());
    }
}
