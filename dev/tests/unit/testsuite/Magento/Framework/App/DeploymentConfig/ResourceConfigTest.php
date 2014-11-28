<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            ]
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
            ]
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
                    'no_connection' => []
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
