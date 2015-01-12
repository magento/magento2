<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class ResourceConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new ResourceConfig([]);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $data = [
            'test' => [
                ResourceConfig::KEY_CONNECTION => 'default',
            ],
        ];
        $expected = [
            'default_setup' => [
                ResourceConfig::KEY_CONNECTION => 'default',
            ],
            'test' => $data['test'],
        ];

        $object = new ResourceConfig($data);
        $this->assertSame($expected, $object->getData());
    }

    public function testEmptyData()
    {
        $data = [
            'default_setup' => [
                ResourceConfig::KEY_CONNECTION => 'default',
            ],
        ];
        $object = new ResourceConfig([]);
        $this->assertSame($data, $object->getData());
    }

    /**
     * @param array $data
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid resource configuration.
     * @dataProvider invalidDataDataProvider
     */
    public function testInvalidData($data)
    {
        new ResourceConfig($data);
    }

    public function invalidDataDataProvider()
    {
        return [
            [
                [
                    'no_connection' => [],
                ],
                [
                    'other' => [
                        'other' => 'default',
                    ]
                ],
            ],
        ];
    }
}
