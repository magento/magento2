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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Class for retrieving runtime configuration from database.
 */
class RuntimeConfigSourceTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var ScopeCodeResolver|MockObject
     */
    private $scopeCodeResolver;

    /**
     * @var Converter|MockObject
     */
    private $converter;

    /**
     * @var Value|MockObject
     */
    private $configItem;

    /**
     * @var Value|MockObject
     */
    private $configItemTwo;

    /**
     * @var RuntimeConfigSource
     */
    private $configSource;
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeCodeResolver = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configItem = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope', 'getPath', 'getValue'])
            ->getMock();
        $this->configItemTwo = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope', 'getPath', 'getValue', 'getScopeId'])
            ->getMock();
        $this->deploymentConfig = $this->createPartialMock(DeploymentConfig::class, ['isDbAvailable']);
        $this->configSource = new RuntimeConfigSource(
            $this->collectionFactory,
            $this->scopeCodeResolver,
            $this->converter,
            $this->deploymentConfig
        );
    }

    public function testGet()
    {
        $this->deploymentConfig->method('isDbAvailable')
            ->willReturn(true);
        $collection = $this->createPartialMock(Collection::class, ['load', 'getIterator']);
        $collection->method('load')
            ->willReturn($collection);
        $collection->method('getIterator')
            ->willReturn(new ArrayIterator([$this->configItem, $this->configItemTwo]));
        $scope = 'websites';
        $scopeCode = 'myWebsites';
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->configItem->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->configItem->expects($this->once())
            ->method('getPath')
            ->willReturn('dev/test/setting');
        $this->configItem->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        $this->configItemTwo->expects($this->exactly(3))
            ->method('getScope')
            ->willReturn($scope);
        $this->configItemTwo->expects($this->once())
            ->method('getScopeId')
            ->willReturn($scopeCode);
        $this->configItemTwo->expects($this->once())
            ->method('getPath')
            ->willReturn('dev/test/setting2');
        $this->configItemTwo->expects($this->once())
            ->method('getValue')
            ->willReturn(false);
        $this->scopeCodeResolver->expects($this->once())
            ->method('resolve')
            ->with($scope, $scopeCode)
            ->willReturnArgument(1);
        $this->converter->expects($this->exactly(2))
            ->method('convert')
            ->withConsecutive(
                [['dev/test/setting' => true]],
                [['dev/test/setting2' => false]]
            )
            ->willReturnOnConsecutiveCalls(
                ['dev/test/setting' => true],
                ['dev/test/setting2' => false]
            );

        $this->assertEquals(
            [
                'default' => [
                    'dev/test/setting' => true
                ],
                'websites' => [
                    'myWebsites' => [
                        'dev/test/setting2' => false
                    ]
                ]
            ],
            $this->configSource->get()
        );
    }

    public function testGetWhenDbIsNotAvailable()
    {
        $this->deploymentConfig->method('isDbAvailable')->willReturn(false);
        $this->assertEquals([], $this->configSource->get());
    }

    public function testGetWhenDbIsEmpty()
    {
        $this->deploymentConfig->method('isDbAvailable')
            ->willReturn(true);
        $collection = $this->createPartialMock(Collection::class, ['load']);
        $collection->method('load')
            ->willThrowException($this->createMock(TableNotFoundException::class));
        $this->collectionFactory->method('create')
            ->willReturn($collection);
        $this->assertEquals([], $this->configSource->get());
    }
}
