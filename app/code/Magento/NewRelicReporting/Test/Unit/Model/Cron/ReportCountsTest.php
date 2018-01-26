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
 *
 * @codingStandardsIgnoreFile
 */
class ReportCountsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportCounts
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productManagementMock;

    /**
     * @var ConfigurableProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableManagementMock;

    /**
     * @var CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryManagementMock;

    /**
     * @var \Magento\NewRelicReporting\Model\CountsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\Counts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsModelMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsCollectionFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsCollectionMock;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->productManagementMock = $this->getMockBuilder('Magento\Catalog\Api\ProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableManagementMock = $this
            ->getMockBuilder('Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryManagementMock = $this->getMockBuilder('Magento\Catalog\Api\CategoryManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsFactoryMock = $this->getMockBuilder('Magento\NewRelicReporting\Model\CountsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->countsModelMock = $this->getMockBuilder('Magento\NewRelicReporting\Model\Counts')
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsCollectionFactoryMock = $this
            ->getMockBuilder('Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionClassName = 'Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection';
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
