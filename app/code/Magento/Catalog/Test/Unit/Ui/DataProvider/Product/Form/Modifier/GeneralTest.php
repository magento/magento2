<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method General getModel
 */
class GeneralTest extends AbstractModifierTest
{
    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var General
     */
    private $generalModifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeRepositoryMock = $this->getMockBuilder(AttributeRepositoryInterface::class)
            ->getMockForAbstractClass();

        $arrayManager = $this->objectManager->getObject(ArrayManager::class);

        $this->generalModifier = $this->objectManager->getObject(
            General::class,
            [
                'attributeRepository' => $this->attributeRepositoryMock,
                'locator' => $this->locatorMock,
                'arrayManager' => $arrayManager,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            General::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock,
            ]
        );
    }

    public function testModifyMeta()
    {
        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(2);
        $this->assertNotEmpty(
            $this->getModel()->modifyMeta(
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
     * @param        array $data
     * @param        int   $defaultStatusValue
     * @param        array $expectedResult
     * @throws       NoSuchEntityException
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyDataNewProduct(array $data, int $defaultStatusValue, array $expectedResult)
    {
        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->getMockForAbstractClass();
        $attributeMock
            ->method('getDefaultValue')
            ->willReturn($defaultStatusValue);
        $this->attributeRepositoryMock
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                ProductAttributeInterface::CODE_STATUS
            )
            ->willReturn($attributeMock);
        $this->assertSame($expectedResult, $this->generalModifier->modifyData($data));
    }

    /**
     * Verify the product attribute status set owhen editing existing product
     *
     * @param        array  $data
     * @param        string $modelId
     * @param        int    $defaultStatus
     * @param        int    $statusAttributeValue
     * @param        array  $expectedResult
     * @throws       NoSuchEntityException
     * @dataProvider modifyDataOfExistingProductDataProvider
     */
    public function testModifyDataOfExistingProduct(
        array $data,
        string $modelId,
        int $defaultStatus,
        int $statusAttributeValue,
        array $expectedResult
    ) {
        $attributeMock = $this->getMockForAbstractClass(AttributeInterface::class);
        $attributeMock->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($defaultStatus);
        $this->attributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                ProductAttributeInterface::CODE_STATUS
            )
            ->willReturn($attributeMock);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);
        $this->productMock->expects($this->any())
            ->method('getStatus')
            ->willReturn($statusAttributeValue);
        $this->assertSame($expectedResult, current($this->generalModifier->modifyData($data)));
    }

    /**
     * @return array
     */
    public function modifyDataOfExistingProductDataProvider(): array
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
    public function modifyDataDataProvider(): array
    {
        return [
            'With default status value' => [
                'data' => [],
                'defaultStatusAttributeValue' => 5,
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
                'defaultStatusAttributeValue' => 0,
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
