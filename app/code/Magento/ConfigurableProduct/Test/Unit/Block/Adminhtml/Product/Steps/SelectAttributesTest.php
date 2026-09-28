<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Steps;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Backend\Block\Widget\Button;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\SelectAttributes;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectAttributesTest extends TestCase
{
    use MockCreationTrait;

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
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->buttonMock = $this->createPartialMockWithReflection(
            Button::class,
            ['toHtml', 'getAuthorization', 'isAllowed']
        );
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);

        $this->contextMock->method('getLayout')->willReturn($this->layoutMock);
        $this->contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->selectAttributes = new SelectAttributes(
            $this->contextMock,
            $this->registryMock
        );
    }

    /**
     * @param bool $isAllowed
     * @param string $result
     *
     *
     * @return void
     */
    #[DataProvider('attributesDataProvider')]
    public function testGetAddNewAttributeButton($isAllowed, $result)
    {
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getStoreId']);
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($productMock);
        
        $this->buttonMock->method('toHtml')->willReturn($result);
        $authorizationMock = $this->createMock(AuthorizationInterface::class);
        $authorizationMock->method('isAllowed')->willReturn($isAllowed);
        $this->buttonMock->method('getAuthorization')->willReturn($authorizationMock);

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($this->buttonMock);

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
