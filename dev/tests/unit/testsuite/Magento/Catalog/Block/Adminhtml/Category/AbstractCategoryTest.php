<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

class AbstractCategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
     */
    protected $category;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            'Magento\Backend\Block\Template\Context',
            [],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->will($this->returnValue($this->storeManagerMock));

        $this->storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));

        $this->category = $this->objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Category\AbstractCategory',
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

}
