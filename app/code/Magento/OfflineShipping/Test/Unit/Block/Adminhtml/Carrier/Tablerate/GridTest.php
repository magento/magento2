<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Carrier\Tablerate;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid;
use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    /**
     * @var Grid
     */
    protected $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Data|MockObject
     */
    protected $backendHelperMock;

    /**
     * @var MockObject
     */
    protected $tablerateMock;

    /**
     * @var MockObject
     */
    protected $context;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context = $objectManager->getObject(
            Context::class,
            ['storeManager' => $this->storeManagerMock]
        );

        $this->backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock =
            $this->getMockBuilder(
                CollectionFactory::class
            )->disableOriginalConstructor()
                ->getMock();

        $this->tablerateMock = $this->getMockBuilder(Tablerate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Grid(
            $this->context,
            $this->backendHelperMock,
            $this->collectionFactoryMock,
            $this->tablerateMock
        );
    }

    public function testSetWebsiteId()
    {
        $websiteId = 1;

        $websiteMock = $this->getMockBuilder(Website::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->assertSame($this->model, $this->model->setWebsiteId($websiteId));
        $this->assertEquals($websiteId, $this->model->getWebsiteId());
    }

    public function testGetWebsiteId()
    {
        $websiteId = 10;

        $websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->assertEquals($websiteId, $this->model->getWebsiteId());

        $this->storeManagerMock->expects($this->never())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->assertEquals($websiteId, $this->model->getWebsiteId());
    }

    public function testSetAndGetConditionName()
    {
        $conditionName = 'someName';
        $this->assertEquals($this->model, $this->model->setConditionName($conditionName));
        $this->assertEquals($conditionName, $this->model->getConditionName());
    }
}
