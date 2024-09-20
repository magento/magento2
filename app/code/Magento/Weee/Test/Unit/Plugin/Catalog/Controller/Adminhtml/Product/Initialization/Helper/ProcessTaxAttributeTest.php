<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Weee\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper\ProcessTaxAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\TestCase;

class ProcessTaxAttributeTest extends TestCase
{
    /**
     * Weee frontend input
     */
    private const WEEE_FRONTEND_INPUT = 'weee';

    /**
     * Text frontend input
     */
    private const TEXT_FRONTEND_INPUT = 'text';

    /**
     * Stub weee attribute code
     */
    private const STUB_WEEE_ATTRIBUTE_CODE = 'weee_1';

    /**
     * Stub weee attribute value
     */
    private const STUB_WEEE_ATTRIBUTE_VALUE = 1122;

    /**
     * @var ProcessTaxAttribute
     */
    private $plugin;

    /**
     * @var Helper|MockObject
     */
    private $subjectMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Product|MockObject
     */
    private $resultMock;

    /**
     * Prepare environment for test
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(Helper::class);
        $this->resultMock = $this->createMock(Product::class);
        $this->productMock = $this->createMock(Product::class);

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(ProcessTaxAttribute::class);
    }

    /**
     * Test afterInitializeFromData when attributes are empty
     */
    public function testAfterInitializeFromDataWhenAttributesAreEmpty()
    {
        $this->resultMock->expects($this->any())->method('getAttributes')
            ->willReturn([]);

        $this->resultMock->expects($this->never())->method('setData');

        $this->plugin->afterInitializeFromData($this->subjectMock, $this->resultMock, $this->productMock, []);
    }

    /**
     * Test afterInitializeFromData when attributes do not include weee frontend input
     */
    public function testAfterInitializeFromDataWhenAttributesDoNotIncludeWeee()
    {
        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createMock(AbstractAttribute::class);

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->willReturn(self::TEXT_FRONTEND_INPUT);

        $this->resultMock->expects($this->any())->method('getAttributes')
            ->willReturn([$attributeMock]);

        $this->resultMock->expects($this->never())->method('setData');

        $this->plugin->afterInitializeFromData($this->subjectMock, $this->resultMock, $this->productMock, []);
    }

    /**
     * Test afterInitializeFromData when attributes include weee
     *
     * @param array $productData
     * @param InvokedCountMatcher $expected
     * @dataProvider afterInitializeFromDataWhenAttributesIncludeWeeeDataProvider
     */
    public function testAfterInitializeFromDataWhenAttributesIncludeWeee($productData, $expected)
    {
        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createMock(AbstractAttribute::class);

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->willReturn(self::WEEE_FRONTEND_INPUT);
        $attributeMock->expects($this->any())->method('getAttributeCode')
            ->willReturn(self::STUB_WEEE_ATTRIBUTE_CODE);
        $this->resultMock->expects($this->any())->method('getAttributes')
            ->willReturn([$attributeMock]);

        $this->resultMock->expects($expected)->method('setData')
            ->with(self::STUB_WEEE_ATTRIBUTE_CODE, [])
            ->willReturnSelf();

        $this->plugin->afterInitializeFromData(
            $this->subjectMock,
            $this->resultMock,
            $this->productMock,
            $productData
        );
    }

    /**
     * ProductData data provider for testAfterInitializeFromDataWhenAttributesIncludeWeee
     *
     * @return array
     */
    public static function afterInitializeFromDataWhenAttributesIncludeWeeeDataProvider()
    {
        return [
            'Product data includes wee' => [
                [
                    self::STUB_WEEE_ATTRIBUTE_CODE => self::STUB_WEEE_ATTRIBUTE_VALUE
                ],
                self::never()
            ],
            'Product data does not include wee' => [
                [],
                self::once()
            ]
        ];
    }
}
