<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RemoteStorage\Test\Unit\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;
use Magento\RemoteStorage\Driver\DriverFactoryPool;
use Magento\RemoteStorage\Driver\RemoteDriverInterface;
use Magento\RemoteStorage\Setup\ConfigOptionsList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigOptionsListTest extends TestCase
{
    /**
     * @var DriverFactoryPool|MockObject
     */
    private $driverFactoryPoolMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ConfigOptionsList
     */
    private $configOptionsList;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function setUp(): void
    {
        $this->driverFactoryPoolMock = $this->getMockBuilder(DriverFactoryPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configOptionsList = new ConfigOptionsList(
            $this->driverFactoryPoolMock,
            $this->loggerMock
        );
    }

    /**
     * @param array $input
     * @param bool $isDeploymentConfigExists
     * @param array $expectedOutput
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $input, bool $isDeploymentConfigExists, array $expectedOutput)
    {
        $deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deploymentConfigMock
            ->expects(static::once())
            ->method('getConfigData')
            ->willReturn($isDeploymentConfigExists);

        $isConnectionToBeTested = $isDeploymentConfigExists && isset(
            $input['remote-storage-region'],
            $input['remote-storage-bucket']
        );

        if ($isConnectionToBeTested) {
            $driverFactoryMock = $this->getMockBuilder(DriverFactoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->driverFactoryPoolMock
                ->expects(static::once())
                ->method('get')
                ->with($input['remote-storage-driver'])
                ->willReturn($driverFactoryMock);

            $remoteDriverMock = $this->getMockBuilder(RemoteDriverInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

            $driverFactoryMock
                ->expects(static::once())
                ->method('createConfigured')
                ->willReturn($remoteDriverMock);

            $testMethodExpectation = $remoteDriverMock->expects(static::once())->method('test');

            $isExceptionExpectedToBeCaught = (bool) count($expectedOutput);

            if ($isExceptionExpectedToBeCaught) {
                $adapterErrorMessage = str_replace('Adapter error: ', '', $expectedOutput[0]);
                $exception = new LocalizedException(__($adapterErrorMessage));
                $testMethodExpectation->willThrowException($exception);
                $this->loggerMock->expects(static::once())->method('critical')->with($exception->getMessage());
            }
        }

        $this->assertEquals(
            $expectedOutput,
            $this->configOptionsList->validate($input, $deploymentConfigMock)
        );
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'Local File Storage Before Deployment Config Exists' => [
                [], false, [],
            ],
            'Local File Storage After Deployment Config Exists' => [
                [], true, [],
            ],
            'Remote Storage Before Deployment Config Exists' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1',
                    'remote-storage-bucket' => 'bucket1',
                ],
                false,
                [],
            ],
            'Remote Storage Missing Region' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-bucket' => 'bucket1',
                ],
                true,
                [
                    'Region is required',
                ],
            ],
            'Remote Storage Missing Bucket' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1',
                ],
                true,
                [
                    'Bucket is required',
                ],
            ],
            'Remote Storage Missing Region and Bucket' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                ],
                true,
                [
                    'Region is required',
                    'Bucket is required',
                ],
            ],
            'Valid Remote Storage Config with Successful Test Connection' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1',
                    'remote-storage-bucket' => 'bucket1',
                    'remote-storage-prefix' => '',
                ],
                true,
                [],
            ],
            'Valid Remote Storage With Unsuccessful Test Connection' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1',
                    'remote-storage-bucket' => 'bucket1',
                    'remote-storage-prefix' => '',
                ],
                true,
                [
                    'Adapter error: [Message from LocalizedException]',
                ]
            ],
        ];
    }

    /**
     * @param array $options
     * @param array $deploymentConfig
     * @param array $expectedConfigArr
     * @dataProvider createConfigProvider
     */
    public function testCreateConfig(array $options, array $deploymentConfig, array $expectedConfigArr)
    {
        $deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deploymentConfigMock
            ->expects(static::once())
            ->method('getConfigData')
            ->willReturn($deploymentConfig);

        $configDataListArr = $this->configOptionsList->createConfig($options, $deploymentConfigMock);

        if (count($configDataListArr)) {
            $this->assertCount(1, $configDataListArr);
            $configDataArr = $configDataListArr[0]->getData();
        } else {
            $configDataArr = [];
        }

        $this->assertEquals(
            $expectedConfigArr,
            $configDataArr
        );
    }

    /**
     * @return array
     */
    public function createConfigProvider()
    {
        return [
            'Remote Storage Options Missing and Remote Storage Deployment Config Present' => [
                [
                    'backend-frontname' => 'admin2022',
                ],
                [
                    'remote_storage' => [
                        'driver' => 'aws-s3',
                    ]
                ],
                // no config data will be passed to write to deployment config
                []
            ],
            'Remote Storage Options Missing and Remote Storage Deployment Config Missing' => [
                [
                    'backend-frontname' => 'admin2022',
                ],
                [],
                [
                    // will create default config with file driver
                    'remote_storage' => [
                        'driver' => 'file',
                    ]
                ]
            ],
            'Remote Storage Options Present and Remote Storage Deployment Config Missing' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1',
                    'remote-storage-bucket' => 'bucket1',
                    'remote-storage-prefix' => 'pre_',
                ],
                [],
                [
                    'remote_storage' => [
                        'driver' => 'aws-s3',
                        'prefix' => 'pre_',
                        'config' => [
                            'bucket' => 'bucket1',
                            'region' => 'us-east-1',
                        ],
                    ]
                ]
            ],
            'Remote Storage Options Present and Remote Storage Deployment Config Present' => [
                [
                    'remote-storage-driver' => 'aws-s3',
                    'remote-storage-region' => 'us-east-1_NEW',
                    'remote-storage-bucket' => 'bucket_NEW',
                ],
                [
                    'remote_storage' => [
                        'driver' => 'aws-s3',
                        'prefix' => 'pre_OLD',
                        'config' => [
                            'bucket' => 'bucket_OLD',
                            'region' => 'us-east-1_OLD',
                        ],
                    ]
                ],
                [
                    'remote_storage' => [
                        'driver' => 'aws-s3',
                        // prefix should be removed as it was not passed in options
                        'config' => [
                            'bucket' => 'bucket_NEW',
                            'region' => 'us-east-1_NEW',
                        ],
                    ]
                ]
            ],
        ];
    }
}
