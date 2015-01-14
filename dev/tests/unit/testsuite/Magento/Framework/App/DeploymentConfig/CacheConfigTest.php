<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class CacheConfigTest extends \PHPUnit_Framework_TestCase
{
    private $data = [
        'frontend' => [
            'default' => [],
        ],
    ];
    public function testGetKey()
    {
        $object = new CacheConfig($this->data);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $object = new CacheConfig($this->data);
        $this->assertSame($this->data, $object->getData());
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
     * @dataProvider invalidDataDataProvider
     */
    public function testInvalidData($data)
    {
        new CacheConfig($data);
    }

    public function invalidDataDataProvider()
    {
        return [
            [
                'frontend' => [
                    'default' => 'not setting array',
                ],
            ],
            [
                'no_frontend' => [
                    'default' => [],
                ]
            ],
            [
                ['frontend' => 'setting']
            ],
        ];
    }
}
