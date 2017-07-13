<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block;

use Magento\Customer\Block\CustomerData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class CustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
    }

    public function testGetExpirableSectionLifetimeReturnsConfigurationValue()
    {
        $block = new CustomerData(
            $this->contextMock,
            [],
            []
        );

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('customer/online_customers/section_data_lifetime', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('10');

        $actualResult = $block->getExpirableSectionLifetime();
        $this->assertSame(10, $actualResult);
    }

    public function testGetExpirableSectionNames()
    {
        $expectedResult = ['cart'];
        $block = new CustomerData(
            $this->contextMock,
            [],
            $expectedResult
        );

        $this->assertEquals($expectedResult, $block->getExpirableSectionNames());
    }
}
