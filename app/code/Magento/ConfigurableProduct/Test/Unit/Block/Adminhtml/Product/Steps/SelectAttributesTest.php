<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Steps;

use Magento\Backend\Block\Widget\Button;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\SelectAttributes;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectAttributesTest extends TestCase
{
    /**
     * @var SelectAttributes
     */
    private $selectAttributes;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Button|MockObject
     */
    private $buttonMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var UrlInterface|MockObject
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
            ->addMethods(['isAllowed'])
            ->onlyMethods(['getAuthorization', 'toHtml'])
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
            ->addMethods(['getStoreId'])
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
    public static function attributesDataProvider()
    {
        return [
            [false, ''],
            [true, 'attribute html']
        ];
    }
}
