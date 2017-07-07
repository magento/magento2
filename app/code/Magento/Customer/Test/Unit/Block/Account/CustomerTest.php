<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Account;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Block\Account\Customer */
    private $block;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $httpContext;

    protected function setUp()
    {
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Customer\Block\Account\Customer::class, ['httpContext' => $this->httpContext]);
    }

    public function customerLoggedInDataProvider()
    {
        return [
            [1, true],
            [0, false],
        ];
    }

    /**
     * @param $isLoggedIn
     * @param $result
     * @dataProvider customerLoggedInDataProvider
     */
    public function testCustomerLoggedIn($isLoggedIn, $result)
    {
        $this->httpContext->expects($this->once())->method('getValue')
            ->with(\Magento\Customer\Model\Context::CONTEXT_AUTH)
            ->willReturn($isLoggedIn);

        $this->assertSame($result, $this->block->customerLoggedIn($isLoggedIn));
    }
}
