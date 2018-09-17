<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Form\Login;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Block\Form\Login\Info
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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    protected function setUp()
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
            'Magento\Framework\Url\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            ['addRequestParam']
        )->getMock();

        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Customer\Block\Form\Login\Info',
            [
                'customerUrl' => $this->customerUrl,
                'checkoutData' => $this->checkoutData,
                'coreUrl' => $this->coreUrl
            ]
        );
    }

    public function testGetExistingCreateAccountUrl()
    {
        $expectedUrl = 'Custom Url';

        $this->block->setCreateAccountUrl($expectedUrl);
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }

    public function testGetCreateAccountUrlWithContext()
    {
        $url = 'Custom Url';
        $expectedUrl = 'Custom Url with context';
        $this->block->setCreateAccountUrl($url);

        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(true));
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $url,
            ['context' => 'checkout']
        )->will(
            $this->returnValue($expectedUrl)
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }

    public function testGetCreateAccountUrl()
    {
        $expectedUrl = 'Custom Url';

        $this->customerUrl->expects($this->any())->method('getRegisterUrl')->will($this->returnValue($expectedUrl));
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }
}
