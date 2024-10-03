<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Block\Adminhtml\Frontend\Currency;

use Magento\Directory\Block\Adminhtml\Frontend\Currency\Base;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Directory\Block\Adminhtml\Frontend\Currency\Base
 */
class BaseTest extends TestCase
{
    const STUB_WEBSITE_PARAM = 'website';

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
     * @var Base
     */
    private $baseCurrency;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->elementMock = $this->createMock(AbstractElement::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->baseCurrency = (new ObjectManagerHelper($this))->getObject(
            Base::class,
            ['_request' => $this->requestMock, '_scopeConfig' => $this->scopeConfigMock]
        );
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
