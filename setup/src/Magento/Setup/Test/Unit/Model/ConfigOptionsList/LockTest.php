<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Setup\Model\ConfigOptionsList\Lock as LockConfigOptionsList;
use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperLock;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LockTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var LockConfigOptionsList
     */
    private $lockConfigOptionsList;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->lockConfigOptionsList = new LockConfigOptionsList();
    }

    /**
     * @return void
     */
    public function testGetOptions()
    {
        $options = $this->lockConfigOptionsList->getOptions();
        $this->assertCount(5, $options);

        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);
        $this->assertEquals(LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER, $options[0]->getName());

        $this->assertArrayHasKey(1, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[1]);
        $this->assertEquals(LockConfigOptionsList::INPUT_KEY_LOCK_DB_PREFIX, $options[1]->getName());

        $this->assertArrayHasKey(2, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[2]);
        $this->assertEquals(LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_HOST, $options[2]->getName());

        $this->assertArrayHasKey(3, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[3]);
        $this->assertEquals(LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_PATH, $options[3]->getName());

        $this->assertArrayHasKey(4, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[4]);
        $this->assertEquals(LockConfigOptionsList::INPUT_KEY_LOCK_FILE_PATH, $options[4]->getName());
    }

    /**
     * @param array $options
     * @param array $expectedResult
     * @dataProvider createConfigDataProvider
     */
    public function testCreateConfig(array $options, array $expectedResult)
    {
        $this->deploymentConfigMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(1);
        $data = $this->lockConfigOptionsList->createConfig($options, $this->deploymentConfigMock);
        $this->assertInstanceOf(ConfigData::class, $data);
        $this->assertTrue($data->isOverrideWhenSave());
        $this->assertSame($expectedResult, $data->getData());
    }

    /**
     * @return array
     */
    public function createConfigDataProvider(): array
    {
        return [
            'Check default values' => [
                'options' => [],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_DB,
                        'config' => [
                            'prefix' => null,
                        ],
                    ],
                ],
            ],
            'Check default value for cache lock' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_CACHE,
                ],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_CACHE,
                    ],
                ],
            ],
            'Check default value for zookeeper lock' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_ZOOKEEPER,
                ],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_ZOOKEEPER,
                        'config' => [
                            'host' => null,
                            'path' => ZookeeperLock::DEFAULT_PATH,
                        ],
                    ],
                ],
            ],
            'Check specific db lock options' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_DB,
                    LockConfigOptionsList::INPUT_KEY_LOCK_DB_PREFIX => 'my_prefix'
                ],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_DB,
                        'config' => [
                            'prefix' => 'my_prefix',
                        ],
                    ],
                ],
            ],
            'Check specific zookeeper lock options' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_ZOOKEEPER,
                    LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_HOST => '123.45.67.89:10',
                    LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_PATH => '/some/path',
                ],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_ZOOKEEPER,
                        'config' => [
                            'host' => '123.45.67.89:10',
                            'path' => '/some/path',
                        ],
                    ],
                ],
            ],
            'Check specific file lock options' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_FILE,
                    LockConfigOptionsList::INPUT_KEY_LOCK_FILE_PATH => '/my/path'
                ],
                'expectedResult' => [
                    'lock' => [
                        'provider' => LockBackendFactory::LOCK_FILE,
                        'config' => [
                            'path' => '/my/path',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $options
     * @param array $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $options, array $expectedResult)
    {
        $this->deploymentConfigMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(1);
        $this->assertSame(
            $expectedResult,
            $this->lockConfigOptionsList->validate($options, $this->deploymentConfigMock)
        );
    }

    /**
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'Wrong lock provider' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => 'SomeProvider',
                ],
                'expectedResult' => [
                    'The lock provider SomeProvider does not exist.',
                ],
            ],
            'Empty host and path for Zookeeper' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_ZOOKEEPER,
                    LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_HOST => '',
                    LockConfigOptionsList::INPUT_KEY_LOCK_ZOOKEEPER_PATH => '',
                ],
                'expectedResult' => extension_loaded('zookeeper')
                    ? [
                        'Zookeeper path needs to be a non-empty string.',
                        'Zookeeper host is should be set.',
                    ]
                    : [
                        'php extension Zookeeper is not installed.',
                        'Zookeeper path needs to be a non-empty string.',
                        'Zookeeper host is should be set.',
                    ],
            ],
            'Empty path for File lock' => [
                'options' => [
                    LockConfigOptionsList::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_FILE,
                    LockConfigOptionsList::INPUT_KEY_LOCK_FILE_PATH => '',
                ],
                'expectedResult' => [
                    'The path needs to be a non-empty string.',
                ],
            ],
        ];
    }
}
