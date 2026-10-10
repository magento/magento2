<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Block\Adminhtml\Frontend\Currency;

use Magento\Directory\Block\Adminhtml\Frontend\Currency\Base;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Directory\Block\Adminhtml\Frontend\Currency\Base
 */
class BaseTest extends TestCase
{
    use MockCreationTrait;

    private const STUB_WEBSITE_PARAM = 'website';

    /**
     * @var AbstractElement|MockObject
     */
    private $elementMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Base|MockObject
     */
    private $baseCurrency;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->elementMock = $this->createMock(AbstractElement::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->baseCurrency = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->addPropertyValue($this->baseCurrency, [
            '_request' => $this->requestMock,
            '_scopeConfig' => $this->scopeConfigMock,
        ], Base::class);
    }

    /**
     * Test case when no Website param provided
     */
    public function testRenderWithoutWebsiteParam()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn('');
        $this->scopeConfigMock->expects($this->never())->method('getValue');

        $result = $this->baseCurrency->render(($this->elementMock));
        $this->assertNotEmpty($result, 'Result should not be empty.');
    }

    /**
     * Test case when Website param is provided and Price Scope is set to Global
     */
    public function testRenderWhenWebsiteParamSetAndPriceScopeGlobal()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::STUB_WEBSITE_PARAM);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(Store::PRICE_SCOPE_GLOBAL);

        $result = $this->baseCurrency->render(($this->elementMock));
        $this->assertEquals('', $result, 'Result should be an empty string.');
    }

    /**
     * Test case when Website param is provided and Price Scope is not Global
     */
    public function testRenderWhenWebsiteParamSetAndPriceScopeOther()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::STUB_WEBSITE_PARAM);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(Store::PRICE_SCOPE_WEBSITE);

        $result = $this->baseCurrency->render(($this->elementMock));
        $this->assertNotEmpty($result, 'Result should not be empty.');
    }
}
