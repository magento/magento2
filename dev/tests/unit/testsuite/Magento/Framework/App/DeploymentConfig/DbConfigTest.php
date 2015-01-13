<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class DbConfigTest extends \PHPUnit_Framework_TestCase
{
    private $data = [
        DbConfig::KEY_PREFIX => 'mg2_',
        'connection' => [
            'default' => [
                DbConfig::KEY_HOST => 'magento.local',
                DbConfig::KEY_NAME => 'magento2',
                DbConfig::KEY_USER => 'mysql_user',
                DbConfig::KEY_PASS => 'mysql_pass',
                DbConfig::KEY_MODEL => 'mysql4',
                DbConfig::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                DbConfig::KEY_ACTIVE => '1',
            ],
        ],
    ];

    public function testGetKey()
    {
        $object = new DbConfig($this->data);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $object = new DbConfig($this->data);
        $this->assertSame($this->data, $object->getData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Database Name field cannot be empty.
     */
    public function testEmptyData()
    {
        new DbConfig([]);
    }

    /**
     * @param array $data
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidDataDataProvider
     */
    public function testInvalidData($data)
    {
        new DbConfig($data);
    }

    public function invalidDataDataProvider()
    {
        return [
            [
                [
                    DbConfig::KEY_PREFIX => 'mg2_',
                    'connection' => [
                            'default' => [
                                DbConfig::KEY_HOST => 'magento.local',
                                DbConfig::KEY_NAME => '',
                                DbConfig::KEY_USER => 'mysql_user',
                                DbConfig::KEY_PASS => 'mysql_pass',
                                DbConfig::KEY_MODEL => 'mysql4',
                                DbConfig::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                                DbConfig::KEY_ACTIVE => '1',
                            ],
                    ],
                ],
            ],
            [
                [
                    DbConfig::KEY_PREFIX => 'mg2*',
                    'connection' => [
                        'default' => [
                            DbConfig::KEY_HOST => 'magento.local',
                            DbConfig::KEY_NAME => 'magento2',
                            DbConfig::KEY_USER => 'mysql_user',
                            DbConfig::KEY_PASS => 'mysql_pass',
                            DbConfig::KEY_MODEL => 'mysql4',
                            DbConfig::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                            DbConfig::KEY_ACTIVE => '1',
                        ],
                    ],
                ]
            ],
            [
                [
                    DbConfig::KEY_PREFIX => '',
                    'connection' => [
                        'default' => [
                            DbConfig::KEY_HOST => 'magento.local',
                            DbConfig::KEY_NAME => 'magento2',
                            DbConfig::KEY_USER => '',
                            DbConfig::KEY_PASS => 'mysql_pass',
                            DbConfig::KEY_MODEL => 'mysql4',
                            DbConfig::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                            DbConfig::KEY_ACTIVE => '1',
                        ],
                    ],
                ]
            ],
            [
                [
                    DbConfig::KEY_PREFIX => '',
                    'connection' => [
                        'default' => [
                            DbConfig::KEY_HOST => '',
                            DbConfig::KEY_NAME => 'magento2',
                            DbConfig::KEY_USER => 'user',
                            DbConfig::KEY_PASS => 'mysql_pass',
                            DbConfig::KEY_MODEL => 'mysql4',
                            DbConfig::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                            DbConfig::KEY_ACTIVE => '1',
                        ],
                    ],
                ]
            ],
        ];
    }
}
