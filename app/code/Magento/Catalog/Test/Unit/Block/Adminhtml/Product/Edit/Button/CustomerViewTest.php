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
class CustomerViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;
    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;
    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;
    /**
     * @var Interceptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;
    /**
     * @var UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
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
                                  ->setMethods(
                                      [
                                          'isSalable',
                                          'getId',
                                      ]
                                  )
                                  ->getMockForAbstractClass();

        $this->registryMock->expects($this->any())
                           ->method('registry')
                           ->with('current_product')
                           ->willReturn($this->productMock);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->storeMock = $this->getMockBuilder(Interceptor::class)
                                ->disableOriginalConstructor()
                                ->setMethods(
                                    [
                                        'getStoreId',
                                        'getCode',
                                    ]
                                )
                                ->getMock();

        $this->actionUrlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
                                           ->disableOriginalConstructor()
                                           ->setMethods(['getUrl'])
                                           ->getMock();

        $this->storeManagerMock->expects($this->once())
                               ->method('getStore')
                               ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
                        ->method('getStoreId')
                        ->willReturn('1');

        $this->storeMock->expects($this->any())
                        ->method('getCode')
                        ->willReturn('default');

        $this->actionUrlBuilderMock->expects($this->once())
                                   ->method('getUrl')
                                   ->willReturn('test_url');
    }

    /**
     * @param string $class
     *
     * @return CustomerView
     */
    protected function getModel($class = CustomerView::class)
    {
        return $this->objectManager->getObject(
            $class,
            [
                'context'          => $this->contextMock,
                'registry'         => $this->registryMock,
                'storeManager'     => $this->storeManagerMock,
                'actionUrlBuilder' => $this->actionUrlBuilderMock,
            ]
        );
    }

    public function testGetButtonData()
    {
        $this->productMock->expects($this->any())
                          ->method('isSalable')
                          ->willReturn(true);

        $this->productMock->expects($this->any())
                          ->method('getId')
                          ->willReturn(1);

        $this->assertEquals(
            [
                'label'    => __('Customer View'),
                'on_click' => sprintf("window.open('%s', '_blank');", 'test_url'),
                'class'    => 'action-secondary',
            ],
            $this->getModel()->getButtonData()
        );
    }

    public function testGetButtonDataDisabledProduct()
    {
        $this->productMock->expects($this->any())
                          ->method('isSalable')
                          ->willReturn(false);

        $this->productMock->expects($this->any())
                          ->method('getId')
                          ->willReturn(1);

        $this->assertEquals(
            [
                'label'    => __('Customer View'),
                'on_click' => sprintf("window.open('%s', '_blank');", 'test_url'),
                'class'    => 'action-secondary',
                'disabled' => 'disabled',
            ],
            $this->getModel()->getButtonData()
        );
    }

    public function testGetButtonDataFalseProduct()
    {
        $this->productMock->expects($this->any())
                          ->method('isSalable')
                          ->willReturn(false);

        $this->productMock->expects($this->any())
                          ->method('getId')
                          ->willReturn(false);

        $this->assertEquals(
            [
                'label'    => __('Customer View'),
                'on_click' => sprintf("window.open('%s', '_blank');", 'test_url'),
                'class'    => 'action-secondary',
                'disabled' => 'disabled',
            ],
            $this->getModel()->getButtonData()
        );
    }
}
