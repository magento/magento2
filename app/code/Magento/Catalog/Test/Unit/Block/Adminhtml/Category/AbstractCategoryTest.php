<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Category;

class AbstractCategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
     */
    protected $category;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->createMock(\Magento\Backend\Block\Template\Context::class);

        $this->requestMock = $this->getMockBuilder(
            \Magento\Framework\App\RequestInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->urlBuilderMock = $this->getMockBuilder(
            \Magento\Framework\UrlInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(
            \Magento\Store\Model\StoreManagerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->category = $this->objectManager->getObject(
            \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory::class,
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

        $this->assertEquals([\Magento\Catalog\Model\Category::TREE_ROOT_ID, 'storeId'], $this->category->getRootIds());
    }
}
