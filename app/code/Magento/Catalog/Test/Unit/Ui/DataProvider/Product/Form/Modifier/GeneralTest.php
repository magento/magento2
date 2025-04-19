<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneralTest extends TestCase
{
    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private AttributeRepositoryInterface $attributeRepositoryMock;

    /**
     * @var ArrayManager|MockObject
     */
    private ArrayManager $arrayManager;

    /**
     * @var LocatorInterface|LocatorInterface&MockObject|MockObject
     */
    private LocatorInterface $locatorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $this->arrayManager = $this->createMock(ArrayManager::class);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testModifyMeta(): void
    {
        $attribute = $this->createMock(Attribute::class);
        $this->attributeRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($attribute);
        $this->arrayManager->expects($this->any())
            ->method('merge')
            ->willReturnArgument(2);
        $store = $this->createMock(Store::class);
        $this->locatorMock->expects($this->any())->method('getStore')->willReturn($store);
        $product = $this->createMock(ProductInterface::class);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);

        $generalModifier = new General($this->locatorMock, $this->arrayManager, $this->attributeRepositoryMock);
        $this->assertNotEmpty(
            $generalModifier->modifyMeta(
                [
                    'first_panel_code' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => 'Test label',
                                ]
                            ],
                        ]
                    ]
                ]
            )
        );
    }

    /**
     * @param array $data
     * @param int $defaultStatusValue
     * @param array $expectedResult
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyDataNewProduct(array $data, int $defaultStatusValue, array $expectedResult): void
    {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock
            ->method('getDefaultValue')
            ->willReturn($defaultStatusValue);
        $this->attributeRepositoryMock
            ->method('get')
            ->willReturn($attributeMock);
        $this->arrayManager->expects($this->any())->method('replace')->willReturn($data);
        $product = $this->createMock(ProductInterface::class);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);
        $generalModifier = new General($this->locatorMock, $this->arrayManager, $this->attributeRepositoryMock);
        $this->assertSame($expectedResult, $generalModifier->modifyData($data));
    }

    /**
     * Verify the product attribute status set owhen editing existing product
     *
     * @param        array  $data
     * @param        string $modelId
     * @param        int    $defaultStatus
     * @param        int    $statusAttributeValue
     * @param        array  $expectedResult
     * @throws       NoSuchEntityException|Exception
     * @dataProvider modifyDataOfExistingProductDataProvider
     */
    public function testModifyDataOfExistingProduct(
        array $data,
        string $modelId,
        int $defaultStatus,
        int $statusAttributeValue,
        array $expectedResult
    ): void {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($defaultStatus);
        $this->attributeRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($attributeMock);
        $product = $this->createMock(ProductInterface::class);
        $product->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);
        $product->expects($this->any())
            ->method('getStatus')
            ->willReturn($statusAttributeValue);
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);
        $this->arrayManager->expects($this->any())->method('replace')->willReturn($data);

        $generalModifier = new General($this->locatorMock, $this->arrayManager, $this->attributeRepositoryMock);
        $this->assertSame($expectedResult, current($generalModifier->modifyData($data)));
    }

    /**
     * @return array
     */
    public static function modifyDataOfExistingProductDataProvider(): array
    {
        return [
            'With enable status value' => [
                'data' => [],
                'modelId' => '1',
                'defaultStatus' => 1,
                'statusAttributeValue' => 1,
                'expectedResult' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 1,
                    ],
                ],
            ],
            'Without disable status value' => [
                'data' => [],
                'modelId' => '1',
                'defaultStatus' => 1,
                'statusAttributeValue' => 2,
                'expectedResult' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 2,
                    ],
                ],
            ],
            'With enable status value with empty modelId' => [
                'data' => [],
                'modelId' => '',
                'defaultStatus' => 1,
                'statusAttributeValue' => 1,
                'expectedResult' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 1,
                    ],
                ],
            ],
            'Without disable status value with empty modelId' => [
                'data' => [],
                'modelId' => '',
                'defaultStatus' => 2,
                'statusAttributeValue' => 2,
                'expectedResult' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function modifyDataDataProvider(): array
    {
        return [
            'With default status value' => [
                'data' => [],
                'defaultStatusValue' => 5,
                'expectedResult' => [
                    null => [
                        General::DATA_SOURCE_DEFAULT => [
                            ProductAttributeInterface::CODE_STATUS => 5,
                        ],
                    ],
                ],
            ],
            'Without default status value' => [
                'data' => [],
                'defaultStatusValue' => 0,
                'expectedResult' => [
                    null => [
                        General::DATA_SOURCE_DEFAULT => [
                            ProductAttributeInterface::CODE_STATUS => 1,
                        ],
                    ],
                ],
            ],
        ];
    }
}
