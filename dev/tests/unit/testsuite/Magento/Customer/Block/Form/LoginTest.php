<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Form;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Block\Form\Login
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\Url
     */
    protected $coreUrl;

    public function setUp()
    {
        $this->customerUrl = $this->getMockBuilder(
            'Magento\Customer\Model\Url'
        )->disableOriginalConstructor()->setMethods(
            ['getRegisterUrl']
        )->getMock();
        $this->checkoutData = $this->getMockBuilder(
            'Magento\Checkout\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            ['isContextCheckout']
        )->getMock();
        $this->coreUrl = $this->getMockBuilder(
            'Magento\Core\Helper\Url'
        )->disableOriginalConstructor()->setMethods(
            ['addRequestParam']
        )->getMock();

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $this->objectManager->getObject(
            'Magento\Customer\Block\Form\Login',
            [
                'customerUrl' => $this->customerUrl,
                'checkoutData' => $this->checkoutData,
                'coreUrl' => $this->coreUrl
            ]
        );
    }

    public function testGetCreateAccountUrl()
    {
        $expectedUrl = 'Custom Url';

        $this->block->setCreateAccountUrl($expectedUrl);
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(true));
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $expectedUrl,
            ['context' => 'checkout']
        )->will(
            $this->returnValue($expectedUrl)
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->block->unsCreateAccountUrl();
        $this->customerUrl->expects($this->any())->method('getRegisterUrl')->will($this->returnValue($expectedUrl));
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->customerUrl->expects($this->any())->method('getRegisterUrl')->will($this->returnValue($expectedUrl));
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(true));
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $expectedUrl,
            ['context' => 'checkout']
        )->will(
            $this->returnValue($expectedUrl)
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }
}
