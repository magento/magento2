<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\Customer;
use Magento\Framework\App\Http\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    /** @var Customer */
    private $block;

    /** @var MockObject */
    private $httpContext;

    protected function setUp(): void
    {
        $this->httpContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = (new ObjectManager($this))
            ->getObject(Customer::class, ['httpContext' => $this->httpContext]);
    }

    /**
     * @return array
     */
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
