<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
class ReportCountsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportCounts
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productManagement;

    /**
     * @var ConfigurableProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableManagement;

    /**
     * @var CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryManagement;

    /**
     * @var \Magento\NewRelicReporting\Model\CountsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\Counts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsCollectionFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countsCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->productManagement = $this->getMockBuilder('Magento\Catalog\Api\ProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableManagement = $this
            ->getMockBuilder('Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryManagement = $this->getMockBuilder('Magento\Catalog\Api\CategoryManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\CountsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->countsModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\Counts')
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsCollectionFactory = $this
            ->getMockBuilder('Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionClassName = 'Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection';
        $this->countsCollection = $this->getMockBuilder($collectionClassName)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'addOrder', 'setPageSize', 'getFirstItem'])
            ->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->setMethods(['formatDate'])
            ->getMock();

        $this->countsFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->countsModel);
        $this->countsModel->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->countsCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->countsCollection);
        $this->countsCollection->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->countsCollection->expects($this->any())
            ->method('addOrder')
            ->willReturnSelf();
        $this->countsCollection->expects($this->any())
            ->method('setPageSize')
            ->willReturnSelf();
        $this->countsCollection->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($this->countsModel);

        $this->model = new ReportCounts(
            $this->config,
            $this->productManagement,
            $this->configurableManagement,
            $this->categoryManagement,
            $this->countsFactory,
            $this->countsCollectionFactory,
            $this->dateTime
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportCountsTestsModuleDisabledFromConfig()
    {
        $this->config->expects($this->once())
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
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->productManagement->expects($this->exactly(2))
            ->method('getCount')
            ->willReturn(2);
        $this->configurableManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(2);
        $this->categoryManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(2);

        $this->countsModel->expects($this->any())
            ->method('getCount')
            ->willReturn(1);
        $this->countsModel->expects($this->any())
            ->method('setEntityId')
            ->willReturnSelf();
        $this->countsModel->expects($this->any())
            ->method('setType')
            ->willReturnSelf();
        $this->countsModel->expects($this->any())
            ->method('setCount')
            ->willReturnSelf();
        $this->countsModel->expects($this->any())
            ->method('setUpdatedAt')
            ->willReturnSelf();
        $this->countsModel->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }
}
