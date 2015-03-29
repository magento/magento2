<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

class ProductRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    private $productData = [
        [
            ProductInterface::SKU => 'simple',
            ProductInterface::NAME => 'Simple Related Product',
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 10,
        ],
        [
            ProductInterface::SKU => 'simple_with_cross',
            ProductInterface::NAME => 'Simple Product With Related Product',
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 10
        ],
    ];

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     */
    public function testGet()
    {
        $productData = $this->productData[0];

        $response = $this->getProduct($productData[ProductInterface::SKU]);
        foreach ([ProductInterface::SKU, ProductInterface::NAME, ProductInterface::PRICE] as $key) {
            $this->assertEquals($productData[$key], $response[$key]);
        }
    }

    protected function getProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku]);
        return $response;
    }

    public function testGetNoSuchEntityException()
    {
        $invalidSku = '(nonExistingSku)';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $invalidSku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $expectedMessage = 'Requested product doesn\'t exist';

        try {
            $this->_webApiCall($serviceInfo, ['sku' => $invalidSku]);
            $this->fail("Expected throwing exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function productCreationProvider()
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                $this->getSimpleProductData(),
                $data
            );
        };
        return [
            [$productBuilder([ProductInterface::TYPE_ID => 'simple', ProductInterface::SKU => 'psku-test-1'])],
            [$productBuilder([ProductInterface::TYPE_ID => 'virtual', ProductInterface::SKU => 'psku-test-2'])],
        ];
    }

    /**
     * @dataProvider productCreationProvider
     */
    public function testCreate($product)
    {
        $response = $this->saveProduct($product);
        $this->assertArrayHasKey(ProductInterface::SKU, $response);
        $this->deleteProduct($product[ProductInterface::SKU]);
    }

    protected function getOptionsData()
    {
        return [
            [
                "product_sku" => "simple",
                "title" => "DropdownOption",
                "type" => "drop_down",
                "sort_order" => 0,
                "is_require" => true,
                "values" => [
                    [
                        "title" => "DropdownOption2_1",
                        "sort_order" => 0,
                        "price" => 3,
                        "price_type" => "fixed",
                    ],
                ],
            ],
            [
                "product_sku" => "simple",
                "title" => "CheckboxOption",
                "type" => "checkbox",
                "values" => [
                    [
                        "title" => "CheckBoxValue1",
                        "price" => 5,
                        "price_type" => "fixed",
                        "sort_order" => 1,
                    ],
                ],
            ],
        ];
    }

    public function testProductOptions()
    {
        //Create product with options
        $productData = $this->getSimpleProductData();
        $optionsDataInput = $this->getOptionsData();
        $productData['options'] = $optionsDataInput;
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));
        $this->assertEquals(1, count($options[0]['values']));
        $this->assertEquals(1, count($options[1]['values']));

        //update the product options
        //Adding a value to option 1, delete an option and create a new option
        $options[0]['values'][] = [
            "title" => "Value2",
            "price" => 6,
            "price_type" => "fixed",
        ];
        $option1Id = $options[0]['option_id'];
        $option2Id = $options[1]['option_id'];
        $options[1] = [
            "product_sku" => "simple",
            "title" => "DropdownOption2",
            "type" => "drop_down",
            "values" => [
                [
                    "title" => "Value3",
                    "price" => 7,
                    "price_type" => "fixed",
                ],
            ],
        ];

        $response['options'] = $options;
        $this->updateProduct($response);

        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));
        $this->assertEquals(2, count($options[0]['values']));
        $this->assertEquals(1, count($options[1]['values']));
        $this->assertEquals($option1Id, $options[0]['option_id']);
        $this->assertTrue($option2Id < $options[1]['option_id']);

        //update product without setting options field, option should not be changed
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        unset($response['options']);
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));

        //update product with empty options, options should be removed
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $response['options'] = [];
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertEmpty($response['options']);

        $this->deleteProduct($productData[ProductInterface::SKU]);
    }

    public function testProductWithMediaGallery()
    {
        $testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
        $encodedImage = base64_encode(file_get_contents($testImagePath));
        //create a product with media gallery
        $filename1 = 'tiny1' . time();
        $filename2 = 'tiny2' . time();
        $productData = $this->getSimpleProductData();
        $productData['media_gallery_entries'] = [
            [
                'position' => 1,
                'disabled' => true,
                'label' => 'tiny1',
                'types' => [],
                'content' => [
                    'mime_type' => 'image/jpeg',
                    'name' => $filename1,
                    'entry_data' => $encodedImage,
                ]
            ],
            [
                'position' => 2,
                'disabled' => false,
                'label' => 'tiny2',
                'types' => ['image', 'small_image'],
                'content' => [
                    'mime_type' => 'image/jpeg',
                    'name' => $filename2,
                    'entry_data' => $encodedImage,
                ]
            ],
        ];
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertArrayHasKey('media_gallery_entries', $response);
        $mediaGalleryEntries = $response['media_gallery_entries'];
        $this->assertEquals(2, count($mediaGalleryEntries));
        $id = $mediaGalleryEntries[0]['id'];
        foreach ($mediaGalleryEntries as &$entry) {
            unset($entry['id']);
        }
        $expectedValue = [
            [
                'label' => 'tiny1',
                'position' => 1,
                'disabled' => true,
                'types' => [],
                'file' => '/t/i/' . $filename1 . '.jpg',
            ],
            [
                'label' => 'tiny2',
                'position' => 2,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'file' => '/t/i/' . $filename2 . '.jpg',
            ],
        ];
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //update the product media gallery
        $response['media_gallery_entries'] = [
            [
                'id' => $id,
                'label' => 'tiny1_new_label',
                'position' => 1,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'file' => '/t/i/' . $filename1 . '.jpg',
            ],
        ];
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $mediaGalleryEntries = $response['media_gallery_entries'];
        $this->assertEquals(1, count($mediaGalleryEntries));
        unset($mediaGalleryEntries[0]['id']);
        $expectedValue = [
            [
                'label' => 'tiny1_new_label',
                'position' => 1,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'file' => '/t/i/' . $filename1 . '.jpg',
            ]
        ];
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //don't set the media_gallery_entries field, existing entry should not be touched
        unset($response['media_gallery_entries']);
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $mediaGalleryEntries = $response['media_gallery_entries'];
        $this->assertEquals(1, count($mediaGalleryEntries));
        unset($mediaGalleryEntries[0]['id']);
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //pass empty array, delete all existing media gallery entries
        $response['media_gallery_entries'] = [];
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertEquals(true, empty($response['media_gallery_entries']));
        $this->deleteProduct($productData[ProductInterface::SKU]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testUpdate()
    {
        $productData = [
            ProductInterface::NAME => 'Very Simple Product', //new name
            ProductInterface::SKU => 'simple', //sku from fixture
        ];
        $product = $this->getSimpleProductData($productData);
        $response =  $this->updateProduct($product);

        $this->assertArrayHasKey(ProductInterface::SKU, $response);
        $this->assertArrayHasKey(ProductInterface::NAME, $response);
        $this->assertEquals($productData[ProductInterface::NAME], $response[ProductInterface::NAME]);
        $this->assertEquals($productData[ProductInterface::SKU], $response[ProductInterface::SKU]);
    }

    protected function updateProduct($product)
    {
        $sku = $product[ProductInterface::SKU];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $product[ProductInterface::SKU] = null;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response =  $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testDelete()
    {
        $response = $this->deleteProduct('simple');
        $this->assertTrue($response);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => 'simple',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['sku']);
        $this->assertEquals('simple', $response['items'][0]['sku']);
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    protected function getSimpleProductData($productData = [])
    {
        return [
            ProductInterface::SKU => isset($productData[ProductInterface::SKU])
                ? $productData[ProductInterface::SKU] : uniqid('sku-', true),
            ProductInterface::NAME => isset($productData[ProductInterface::NAME])
                ? $productData[ProductInterface::NAME] : uniqid('sku-', true),
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 3.62,
            ProductInterface::STATUS => 1,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes' => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ]
        ];
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function saveProduct($product)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Delete Product
     *
     * @param string $sku
     * @return boolean
     */
    protected function deleteProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $sku]) : $this->_webApiCall($serviceInfo);
    }
}
