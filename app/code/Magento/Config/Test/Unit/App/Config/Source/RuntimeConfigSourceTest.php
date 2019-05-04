<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\RuntimeConfigSource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;

/**
 * Test Class for retrieving runtime configuration from database.
 * @package Magento\Config\Test\Unit\App\Config\Source
 */
class RuntimeConfigSourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var ScopeCodeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeCodeResolver;

    /**
     * @var Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configItem;

    /**
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configItemTwo;

    /**
     * @var RuntimeConfigSource
     */
    private $configSource;

    public function setUp()
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
        $this->configSource = new RuntimeConfigSource(
            $this->collectionFactory,
            $this->scopeCodeResolver,
            $this->converter
        );
    }

    public function testGet()
    {
        $scope = 'websites';
        $scopeCode = 'myWebsites';
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn([$this->configItem, $this->configItemTwo]);
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
}
