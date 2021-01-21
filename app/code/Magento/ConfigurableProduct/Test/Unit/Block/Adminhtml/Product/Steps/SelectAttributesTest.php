<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Steps;

use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\SelectAttributes;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\UrlInterface;

class SelectAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectAttributes
     */
    private $selectAttributes;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var Button|\PHPUnit\Framework\MockObject\MockObject
     */
    private $buttonMock;

    /**
     * @var LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMock();
        $this->buttonMock = $this->getMockBuilder(Button::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed', 'getAuthorization', 'toHtml'])
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->selectAttributes = new SelectAttributes(
            $this->contextMock,
            $this->registryMock
        );
    }

    /**
     * @param bool $isAllowed
     * @param string $result
     *
     * @dataProvider attributesDataProvider
     *
     * @return void
     */
    public function testGetAddNewAttributeButton($isAllowed, $result)
    {
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($productMock);
        $this->buttonMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($result);

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($this->buttonMock);
        $this->buttonMock->expects($this->once())
            ->method('getAuthorization')
            ->willReturnSelf();
        $this->buttonMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Catalog::attributes_attributes')
            ->willReturn($isAllowed);

        $this->assertEquals($result, $this->selectAttributes->getAddNewAttributeButton());
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        return [
            [false, ''],
            [true, 'attribute html']
        ];
    }
}
