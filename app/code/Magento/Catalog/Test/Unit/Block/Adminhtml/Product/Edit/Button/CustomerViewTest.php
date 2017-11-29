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

    protected $storeManagerMock;

    protected $actionUrlBuilderMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(Context::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->registryMock = $this->getMockBuilder(Registry::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->productMock = $this->getMockBuilder(ProductInterface::class)
                                  ->setMethods(['isSalable', 'getId'])
                                  ->getMockForAbstractClass();

        $this->registryMock->expects($this->any())
                           ->method('registry')
                           ->with('current_product')
                           ->willReturn($this->productMock);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->actionUrlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
                                           ->disableOriginalConstructor()
                                           ->setMethods(['getUrl'])
                                           ->getMock();
    }

    /**
     * @param string $class
     * @return Generic
     */
    protected function getModel($class = CustomerView::class)
    {
        return $this->objectManager->getObject($class, [
            'context' => $this->contextMock,
            'registry' => $this->registryMock,
            'storeManager' => $this->storeManagerMock,
            'actionUrlBuilder' => $this->actionUrlBuilderMock,
        ]);
    }

    public function testGetButtonData()
    {
        $this->storeManagerMock->expects($this->once())
                               ->method('getStore')
                               ->with('')
                               ->willReturn(true);

        $this->actionUrlBuilderMock->expects($this->once())
                                   ->method('getUrl')
                                   ->with('catalog/product/view', $this->productMock, 1, 'default')
                                   ->willReturn('test_url');

        $this->assertEquals(
            [
                'label' => __('Customer View'),
                'on_click' => sprintf("window.open('%s', '_blank');", 'test_url'),
                'class' => 'action-secondary',
            ],
            $this->getModel()->getButtonData()
        );
    }
}
