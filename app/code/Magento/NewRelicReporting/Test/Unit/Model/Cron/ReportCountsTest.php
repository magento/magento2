<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\ProductManagementInterface;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Counts;
use Magento\NewRelicReporting\Model\CountsFactory;
use Magento\NewRelicReporting\Model\Cron\ReportCounts;
use Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection;
use Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportCountsTest extends TestCase
{
    /**
     * @var ReportCounts
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var ProductManagementInterface|MockObject
     */
    protected $productManagementMock;

    /**
     * @var ConfigurableProductManagementInterface|MockObject
     */
    protected $configurableManagementMock;

    /**
     * @var CategoryManagementInterface|MockObject
     */
    protected $categoryManagementMock;

    /**
     * @var CountsFactory|MockObject
     */
    protected $countsFactoryMock;

    /**
     * @var Counts|MockObject
     */
    protected $countsModelMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $countsCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $countsCollectionMock;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->productManagementMock = $this->getMockBuilder(ProductManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configurableManagementMock = $this
            ->getMockBuilder(ConfigurableProductManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryManagementMock = $this->getMockBuilder(CategoryManagementInterface::class)
            ->setMethods(['getCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->countsFactoryMock = $this->getMockBuilder(CountsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->countsModelMock = $this->getMockBuilder(Counts::class)
            ->setMethods(['getCount', 'load', 'setEntityId', 'setType', 'setCount', 'setUpdatedAt', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->countsCollectionFactoryMock = $this
            ->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionClassName = Collection::class;
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
