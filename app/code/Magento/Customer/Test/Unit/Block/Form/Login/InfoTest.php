<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Form\Login;

use Magento\Checkout\Helper\Data;
use Magento\Customer\Block\Form\Login\Info;
use Magento\Customer\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    protected $block;

    /**
     * @var MockObject|\Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var MockObject|Data
     */
    protected $checkoutData;

    /**
     * @var MockObject|\Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    protected function setUp(): void
    {
        $this->customerUrl = $this->getMockBuilder(
            Url::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getRegisterUrl']
            )->getMock();
        $this->checkoutData = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['isContextCheckout']
            )->getMock();
        $this->coreUrl = $this->getMockBuilder(
            \Magento\Framework\Url\Helper\Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['addRequestParam']
            )->getMock();

        $this->block = (new ObjectManager($this))->getObject(
            Info::class,
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
