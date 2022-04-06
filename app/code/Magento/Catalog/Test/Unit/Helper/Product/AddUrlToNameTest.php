<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\AddUrlToName;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class AddUrlToNameTest
 * @package Magento\Catalog\Test\Unit\Helper\Product
 */
class AddUrlToNameTest extends TestCase
{
    /**
     * Sample product url
     */
    const SAMPLE_PRODUCT_URL = 'Sample URL';

    /**
     * Sample product name
     */
    const SAMPLE_PRODUCT_NAME = 'Sample product';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $linkedProductMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->linkedProductMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMockForAbstractClass();

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn(self::SAMPLE_PRODUCT_URL);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturn(self::SAMPLE_PRODUCT_NAME);
    }

    /**
     * Test checks AddUrlToName type
     */
    public function testCheckType(): void
    {
        $this->assertInstanceOf(AddUrlToName::class, $this->getModel());
    }

    /**
     * Test checks modifyData method
     *
     * @param string $expectedData
     * @dataProvider getDataDataProvider
     */
    public function testAddUrlToName(string $expectedData): void
    {
        $this->assertSame($expectedData, $this->getModel()->addUrlToName($this->linkedProductMock));
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            0 => [
                'expectedData' =>
                    '<a href="javascript:;" onclick="window.open(\'Sample URL\', \'_blank\');">Sample product</a>',
            ]
        ];
    }

    /**
     * @return object
     */
    private function getModel(): AddUrlToName
    {
        return $this->objectManager->getObject(AddUrlToName::class, [
            'locator' => $this->locatorMock,
            'urlBuilder' => $this->urlBuilderMock,
            'escaper' => $this->escaperMock,
        ]);
    }
}
