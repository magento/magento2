<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class GeneralTest
 *
 * @method General getModel
 */
class GeneralTest extends AbstractModifierTest
{
    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var General
     */
    private $generalModifier;

    protected function setUp()
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
     * @throws       \Magento\Framework\Exception\NoSuchEntityException
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testModifyDataExistingProduct()
    {
        $data = [];
        $modelId = 1;
        $defaultStatusValue = 1;
        $expectedResult = [
            'enabledProductStatus' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 1,
                    ],
            ],
            'disabledProductStatus' => [
                    General::DATA_SOURCE_DEFAULT => [
                        ProductAttributeInterface::CODE_STATUS => 2,
                    ],
            ],
        ];
        $enabledProductStatus = 1;
        $disabledProductStatus = 2;
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
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);
        $this->productMock->expects($this->any())
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls($enabledProductStatus, $disabledProductStatus);
        $this->assertSame($expectedResult['enabledProductStatus'], current($this->generalModifier->modifyData($data)));
        $this->assertSame($expectedResult['disabledProductStatus'], current($this->generalModifier->modifyData($data)));
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
