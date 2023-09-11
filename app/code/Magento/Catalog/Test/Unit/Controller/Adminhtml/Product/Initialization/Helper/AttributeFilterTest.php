<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeFilterTest extends TestCase
{
    /**
     * @var AttributeFilter
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(AttributeFilter::class);
    }

    /**
     * @param array $requestProductData
     * @param array $useDefaults
     * @param array $expectedProductData
     * @param array $initialProductData
     * @param mixed $attributeList
     * @dataProvider setupInputDataProvider
     */
    public function testPrepareProductAttributes(
        array $requestProductData,
        array $useDefaults,
        array $expectedProductData,
        array $initialProductData,
        mixed $attributeList
    ): void {
        /** @var MockObject | Product $productMockMap */
        $productMockMap = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getAttributes'])
            ->getMock();

        if (!empty($initialProductData)) {
            $productMockMap->expects($this->any())->method('getData')->willReturnMap($initialProductData);
        }

        if ($useDefaults) {
            $productMockMap
                ->expects($this->once())
                ->method('getAttributes')
                ->willReturn(
                    $this->getProductAttributesMock($useDefaults)
                );
        } elseif ($attributeList) {
            $productMockMap
                ->expects($this->once())
                ->method('getAttributes')
                ->willReturn($attributeList);
        }

        $actualProductData = $this->model->prepareProductAttributes($productMockMap, $requestProductData, $useDefaults);
        $this->assertEquals($expectedProductData, $actualProductData);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setupInputDataProvider(): array
    {
        return [
            'test case for create new product without custom attribute' => [
                'productData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100',
                    'description' => '',
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100',
                ],
                'initialProductData' => [],
                'attributeList' => null
            ],
            'test case for create new product with custom attribute' => [
                'productData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100',
                    'description' => 'testDescription',
                    'custom_attr' => ''
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100',
                    'description' => 'testDescription',
                    'custom_attr' => ''
                ],
                'initialProductData' => [],
                'attributeList' => [
                    'custom_attr' => new DataObject(
                        ['frontend_type' => 'frontend', 'backend_type' => 'backend',
                            'is_user_defined' => '1', 'is_required' => '0',
                            'additional_data' => 'swatch_input_type: visual'
                        ]
                    )
                ]
            ],
            'test case for update product without use_defaults' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => '',
                    'special_price' => null,
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'special_price' => null,
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null],
                ],
                'attributeList' => null
            ],
            'test case for update product with custom attribute' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'testDescription',
                    'custom_attr' => '',
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'testDescription',
                    'custom_attr' => '',
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['custom_attr', ''],
                ],
                'attributeList' => [
                    'custom_attr' => new DataObject(
                        ['frontend_type' => 'frontend', 'backend_type' => 'backend',
                            'is_user_defined' => '1', 'is_required' => '0',
                            'additional_data' => 'swatch_input_type: visual'
                        ]
                    )
                ]
            ],
            'test case for update product without use_defaults_2' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'updated description',
                    'special_price' => null,
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'updated description',
                    'special_price' => null,
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null],
                ],
                'attributeList' => null
            ],
            'test case for update product with use_defaults' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => '',
                    'special_price' => null,
                ],
                'useDefaults' => [
                    'description' => '0',
                ],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'special_price' => null,
                    'description' => '',
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null],
                    ['description', 'descr text'],
                ],
                'attributeList' => null
            ],
            'test case for update product with use_defaults_2' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'description' => 'descr modified',
                    'special_price' => '100',
                ],
                'useDefaults' => [
                    'description' => '0',
                ],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'description' => 'descr modified',
                ],
                'initialProductData' => [
                    ['name', null, 'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['description', null, 'descr text'],
                ],
                'attributeList' => null
            ],
            'test case for update product with use_defaults_3' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'description' => 'descr modified',
                ],
                'useDefaults' => [
                    'description' => '1',
                ],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'description' => false,
                ],
                'initialProductData' => [
                    ['name', null, 'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['description', null, 'descr text'],
                ],
                'attributeList' => null
            ],
            'test case for update product with empty string attribute' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'custom_attribute' => '',
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'custom_attribute' => '',
                ],
                'initialProductData' => [
                    ['name', null, 'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['custom_attribute', null, '0'],
                ],
                'attributeList' => null
            ],
            'update_product_with_multi_select_attribute' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'multi_select_attribute' => 'test',
                ],
                'useDefaults' => ['multi_select_attribute' => '1'],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'multi_select_attribute' => false,
                ],
                'initialProductData' => [
                    ['name', null, 'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['multi_select_attribute', null, 'test'],
                ],
                'attributeList' => null
            ],
        ];
    }

    /**
     * @param array $useDefaults
     * @return array
     */
    private function getProductAttributesMock(array $useDefaults): array
    {
        $returnArray = [];
        foreach ($useDefaults as $attributecode => $isDefault) {
            if ($isDefault === '1') {
                /** @var Attribute | MockObject $attribute */
                $attribute = $this->getMockBuilder(Attribute::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                $attribute->expects($this->any())
                    ->method('getBackendType')
                    ->willReturn('varchar');

                $returnArray[$attributecode] = $attribute;
            }
        }
        return $returnArray;
    }
}
