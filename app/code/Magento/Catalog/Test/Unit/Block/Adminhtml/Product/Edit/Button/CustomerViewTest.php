<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\CustomerView;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Action\UrlBuilder;
use Magento\Store\Model\Store\Interceptor;

/**
 * Class CustomerViewTest
 */
class CustomerViewTests extends \PHPUnit\Framework\TestCase
{
    public function testGetButtonData()
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionUrlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $customerView = new CustomerView(
            $contextMock,
            $registryMock,
            $storeManagerMock,
            $actionUrlBuilderMock
        );
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getId', 'isSalable'])
            ->getMockForAbstractClass();
        $productMock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(12);
        $registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($productMock);
        $actionUrlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('test_url');
        $this->assertEquals(
            [
                'label' => __('Customer View'),
                'on_click' => sprintf("window.open('%s', '_blank');", 'test_url'),
                'class' => 'action-secondary',
            ],
            $customerView->getButtonData()
        );
    }
}
