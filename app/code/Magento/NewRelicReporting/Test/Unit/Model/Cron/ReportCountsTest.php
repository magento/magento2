<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Cron\ReportCounts;
use Magento\Catalog\Api\ProductManagementInterface;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\Catalog\Api\CategoryManagementInterface;

/**
 * Class ReportCountsTest
 */
class ReportCountsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReportCounts
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var ProductManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productManagementMock;

    /**
     * @var ConfigurableProductManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurableManagementMock;

    /**
     * @var CategoryManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryManagementMock;

    /**
     * @var \Magento\NewRelicReporting\Model\CountsFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countsFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\Counts|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countsModelMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory
     * |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countsCollectionFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countsCollectionMock;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->productManagementMock = $this->getMockBuilder(\Magento\Catalog\Api\ProductManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableManagementMock = $this
            ->getMockBuilder(\Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryManagementMock = $this->getMockBuilder(\Magento\Catalog\Api\CategoryManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->countsFactoryMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\CountsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->countsModelMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Counts::class)
            ->setMethods(['getCount', 'load', 'setEntityId', 'setType', 'setCount', 'setUpdatedAt', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsCollectionFactoryMock = $this
            ->getMockBuilder(\Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionClassName = \Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection::class;
        $this->countsCollectionMock = $this->getMockBuilder($collectionClassName)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'addOrder', 'setPageSize', 'getFirstItem'])
            ->getMock();

        $this->countsFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->countsModelMock);
        $this->countsModelMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->countsCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->countsCollectionMock);
        $this->countsCollectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->countsCollectionMock->expects($this->any())
            ->method('addOrder')
            ->willReturnSelf();
        $this->countsCollectionMock->expects($this->any())
            ->method('setPageSize')
            ->willReturnSelf();
        $this->countsCollectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($this->countsModelMock);

        $this->model = new ReportCounts(
            $this->configMock,
            $this->productManagementMock,
            $this->configurableManagementMock,
            $this->categoryManagementMock,
            $this->countsFactoryMock,
            $this->countsCollectionFactoryMock
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportCountsTestsModuleDisabledFromConfig()
    {
        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled
     *
     * @return void
     */
    public function testReportCountsTest()
    {
        $this->configMock->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->productManagementMock->expects($this->exactly(2))
            ->method('getCount')
            ->willReturn(2);
        $this->configurableManagementMock->expects($this->once())
            ->method('getCount')
            ->willReturn(2);
        $this->categoryManagementMock->expects($this->once())
            ->method('getCount')
            ->willReturn(2);

        $this->countsModelMock->expects($this->any())
            ->method('getCount')
            ->willReturn(1);
        $this->countsModelMock->expects($this->any())
            ->method('setEntityId')
            ->willReturnSelf();
        $this->countsModelMock->expects($this->any())
            ->method('setType')
            ->willReturnSelf();
        $this->countsModelMock->expects($this->any())
            ->method('setCount')
            ->willReturnSelf();
        $this->countsModelMock->expects($this->any())
            ->method('setUpdatedAt')
            ->willReturnSelf();
        $this->countsModelMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }
}
