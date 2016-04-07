<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Shipping
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $layout;

    protected function setUp()
    {
        $this->context = $this->getMock('\Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->configProvider = $this->getMock('\Magento\Checkout\Model\CompositeConfigProvider', [], [], '', false);
        $this->layoutProcessor = $this->getMock('\Magento\Checkout\Block\Checkout\LayoutProcessorInterface');
        $this->layout = [
            'components' => [
                'firstComponent' => ['param' => 'value'],
                'secondComponent' => ['param' => 'value'],
            ]
        ];

        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->context->expects($this->once())->method('getStoreManager')->willReturn($this->storeManager);

        $this->model = new \Magento\Checkout\Block\Cart\Shipping(
            $this->context,
            $this->customerSession,
            $this->checkoutSession,
            $this->configProvider,
            [$this->layoutProcessor],
            ['jsLayout' => $this->layout]
        );
    }

    public function testGetCheckoutConfig()
    {
        $config = ['param' => 'value'];
        $this->configProvider->expects($this->once())->method('getConfig')->willReturn($config);
        $this->assertEquals($config, $this->model->getCheckoutConfig());
    }

    public function testGetJsLayout()
    {
        $layoutProcessed = $this->layout;
        $layoutProcessed['components']['thirdComponent'] = ['param' => 'value'];

        $this->layoutProcessor->expects($this->once())
            ->method('process')
            ->with($this->layout)
            ->willReturn($layoutProcessed);
        $this->assertEquals(
            \Zend_Json::encode($layoutProcessed),
            $this->model->getJsLayout()
        );
    }

    public function testGetBaseUrl()
    {
        $baseUrl = 'baseUrl';
        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getBaseUrl'], [], '', false);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->assertEquals($baseUrl, $this->model->getBaseUrl());
    }
}
