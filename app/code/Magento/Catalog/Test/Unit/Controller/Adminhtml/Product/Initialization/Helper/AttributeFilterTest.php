<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;
use Magento\Catalog\Model\Product;

class AttributeFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeFilter
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(AttributeFilter::class);
    }

    /**
     * @param array $requestProductData
     * @param array $useDefaults
     * @param array $expectedProductData
     * @param array $initialProductData
     * @dataProvider setupInputDataProvider
     */
    public function testPrepareProductAttributes(
        $requestProductData,
        $useDefaults,
        $expectedProductData,
        $initialProductData
    ) {
        $productMockMap = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        if (!empty($initialProductData)) {
            $productMockMap->expects($this->any())->method('getData')->willReturnMap($initialProductData);
        }

        $actualProductData = $this->model->prepareProductAttributes($productMockMap, $requestProductData, $useDefaults);
        $this->assertEquals($expectedProductData, $actualProductData);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setupInputDataProvider()
    {
        return [
            'create_new_product' => [
                'productData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100',
                    'description' => ''
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName',
                    'sku' => 'testSku',
                    'price' => '100'
                ],
                'initialProductData' => []
            ],
            'update_product_without_use_defaults' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => '',
                    'special_price' => null
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'special_price' => null
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null]
                ]
            ],
            'update_product_without_use_defaults_2' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'updated description',
                    'special_price' => null
                ],
                'useDefaults' => [],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => 'updated description',
                    'special_price' => null
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null]
                ]
            ],
            'update_product_with_use_defaults' => [
                'productData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'description' => '',
                    'special_price' => null
                ],
                'useDefaults' => [
                    'description' => '0'
                ],
                'expectedProductData' => [
                    'name' => 'testName2',
                    'sku' => 'testSku2',
                    'price' => '101',
                    'special_price' => null,
                    'description' => ''
                ],
                'initialProductData' => [
                    ['name', 'testName2'],
                    ['sku', 'testSku2'],
                    ['price', '101'],
                    ['special_price', null],
                    ['description', 'descr text']
                ]
            ],
            'update_product_with_use_defaults_2' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'description' => 'descr modified',
                    'special_price' => '100'
                ],
                'useDefaults' => [
                    'description' => '0'
                ],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                    'description' => 'descr modified'
                ],
                'initialProductData' => [
                    ['name', null,'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['description', null, 'descr text']
                ]
            ],
            'update_product_with_use_defaults_3' => [
                'requestProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100'
                ],
                'useDefaults' => [
                    'description' => '1'
                ],
                'expectedProductData' => [
                    'name' => 'testName3',
                    'sku' => 'testSku3',
                    'price' => '103',
                    'special_price' => '100',
                ],
                'initialProductData' => [
                    ['name', null,'testName2'],
                    ['sku', null, 'testSku2'],
                    ['price', null, '101'],
                    ['description', null, 'descr text']
                ]
            ],
        ];
    }
}
