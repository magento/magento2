<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Form\Login;

class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Form\Login\Info
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    protected function setUp(): void
    {
        $this->customerUrl = $this->getMockBuilder(
            \Magento\Customer\Model\Url::class
        )->disableOriginalConstructor()->setMethods(
            ['getRegisterUrl']
        )->getMock();
        $this->checkoutData = $this->getMockBuilder(
            \Magento\Checkout\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['isContextCheckout']
        )->getMock();
        $this->coreUrl = $this->getMockBuilder(
            \Magento\Framework\Url\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['addRequestParam']
        )->getMock();

        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Customer\Block\Form\Login\Info::class,
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
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->willReturn(false);
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }

    public function testGetCreateAccountUrlWithContext()
    {
        $url = 'Custom Url';
        $expectedUrl = 'Custom Url with context';
        $this->block->setCreateAccountUrl($url);

        $this->checkoutData->expects($this->any())->method('isContextCheckout')->willReturn(true);
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $url,
            ['context' => 'checkout']
        )->willReturn(
            $expectedUrl
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }

    public function testGetCreateAccountUrl()
    {
        $expectedUrl = 'Custom Url';

        $this->customerUrl->expects($this->any())->method('getRegisterUrl')->willReturn($expectedUrl);
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->willReturn(false);
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }
}
