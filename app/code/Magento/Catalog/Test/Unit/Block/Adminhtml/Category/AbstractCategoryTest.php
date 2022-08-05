<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractCategoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var AbstractCategory
     */
    protected $category;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);

        $this->requestMock = $this->getMockBuilder(
            RequestInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->urlBuilderMock = $this->getMockBuilder(
            UrlInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(
            StoreManagerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->category = $this->objectManager->getObject(
            AbstractCategory::class,
            [
                'context' => $this->contextMock,
            ]
        );
    }

    /**
     * @covers \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory::getStore
     * @covers \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory::getSaveUrl
     */
    public function testGetSaveUrl()
    {
        $storeId = 23;
        $saveUrl = 'save URL';
        $params = ['_current' => false, '_query' => false, 'store' => $storeId];

        $this->requestMock->expects($this->once())->method('getParam')->with('store')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/*/save', $params)
            ->willReturn($saveUrl);

        $this->assertEquals($saveUrl, $this->category->getSaveUrl());
    }

    public function testGetRootIdsFromCache()
    {
        $this->category->setData('root_ids', ['ids']);
        $this->storeManagerMock->expects($this->never())->method('getGroups');

        $this->assertEquals(['ids'], $this->category->getRootIds());
    }

    public function testGetRootIds()
    {
        $this->storeManagerMock->expects($this->once())->method('getGroups')->willReturn([$this->storeMock]);
        $this->storeMock->expects($this->once())->method('getRootCategoryId')->willReturn('storeId');

        $this->assertEquals([Category::TREE_ROOT_ID, 'storeId'], $this->category->getRootIds());
    }
}
