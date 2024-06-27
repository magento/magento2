<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Source;

use ArrayIterator;
use Magento\Config\App\Config\Source\RuntimeConfigSource;
use Magento\Config\Model\ResourceModel\Config\Data\Collection;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Class for retrieving runtime configuration from database.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuntimeConfigSourceTest extends TestCase
{
    /**
     * @var RuntimeConfigSource
     */
    private $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var ScopeCodeResolver|MockObject
     */
    private $scopeCodeResolverMock;

    /**
     * @var Converter|MockObject
     */
    private $converterMock;

    /**
     * @var Value|MockObject
     */
    private $configItemMock;

    /**
     * @var Value|MockObject
     */
    private $configItemMockTwo;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeCodeResolverMock = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configItemMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->addMethods(['getScope', 'getPath', 'getValue'])
            ->getMock();
        $this->configItemMockTwo = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->addMethods(['getScope', 'getPath', 'getValue', 'getScopeId'])
            ->getMock();
        $this->deploymentConfigMock = $this->createPartialMock(
            DeploymentConfig::class,
            ['isDbAvailable']
        );
        $this->model = $objectManager->getObject(
            RuntimeConfigSource::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'scopeCodeResolver' => $this->scopeCodeResolverMock,
                'converter' => $this->converterMock,
                'deploymentConfig' => $this->deploymentConfigMock,
            ]
        );
    }

    /**
     * Test get initial data.
     *
     * @return void
     */
    public function testGet(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $collection = $this->createPartialMock(Collection::class, ['load', 'getIterator']);
        $collection->expects($this->once())
            ->method('load')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$this->configItemMock, $this->configItemMockTwo]));
        $scope = 'websites';
        $scopeCode = 'myWebsites';
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->configItemMock->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->configItemMock->expects($this->once())
            ->method('getPath')
            ->willReturn('dev/test/setting');
        $this->configItemMock->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        $this->configItemMockTwo->expects($this->exactly(4))
            ->method('getScope')
            ->willReturn($scope);
        $this->configItemMockTwo->expects($this->once())
            ->method('getScopeId')
            ->willReturn($scopeCode);
        $this->configItemMockTwo->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn('dev/test/setting2');
        $this->configItemMockTwo->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn(false);
        $this->scopeCodeResolverMock->expects($this->once())
            ->method('resolve')
            ->with($scope, $scopeCode)
            ->willReturnArgument(1);
        $this->converterMock->expects($this->exactly(3))
            ->method('convert')
            ->willReturnCallback(function ($args) {
                if ($args === ['dev/test/setting' => true]) {
                    return ['dev/test/setting' => true];
                } elseif ($args === ['dev/test/setting2' => false]) {
                    return ['dev/test/setting2' => false];
                }
            });

        $this->assertEquals(
            [
                'default' => [
                    'dev/test/setting' => true
                ],
                'websites' => [
                    'myWebsites' => [
                        'dev/test/setting2' => false
                    ],
                    'mywebsites' => [
                        'dev/test/setting2' => false
                    ]
                ]
            ],
            $this->model->get()
        );
    }

    /**
     * Test get with not available db
     *
     * @return void
     */
    public function testGetWhenDbIsNotAvailable(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);
        $this->assertEquals([], $this->model->get());
    }

    /**
     * Test get with empty db
     *
     * @return void
     */
    public function testGetWhenDbIsEmpty(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $collection = $this->createPartialMock(Collection::class, ['load']);
        $collection->expects($this->once())
            ->method('load')
            ->willThrowException($this->createMock(TableNotFoundException::class));
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals([], $this->model->get());
    }

    /**
     * Test get value for specified config
     *
     * @dataProvider configDataProvider
     *
     * @param string $path
     * @param array $configData
     * @param string $expectedResult
     * @return void
     */
    public function testGetConfigValue(string $path, array $configData, string $expectedResult): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);

        $collection = $this->createPartialMock(Collection::class, ['load', 'getIterator']);
        $collection->expects($this->once())
            ->method('load')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$this->configItemMock]));

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->configItemMock->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->configItemMock->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $this->converterMock->expects($this->once())
            ->method('convert')
            ->willReturn($configData);

        $this->assertEquals($expectedResult, $this->model->get($path));
    }

    /**
     * DataProvider for testGetConfigValue
     *
     * @return array
     */
    public static function configDataProvider(): array
    {
        return [
            'config value 0' => ['default/test/option', ['test' => ['option' => 0]], '0'],
            'config value blank' => ['default/test/option', ['test' => ['option' => '']], ''],
            'config value null' => ['default/test/option', ['test' => ['option' => null]], ''],
        ];
    }
}
