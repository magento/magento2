<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Store;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

/**
 * @magentoAppIsolation enabled
 */
class ProductRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    const KEY_TIER_PRICES = 'tier_prices';
    const KEY_SPECIAL_PRICE = 'special_price';

    /**
     * @var array
     */
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

    /**
     * @param string $sku
     * @param string|null $storeCode
     * @return array|bool|float|int|string
     */
    protected function getProduct($sku, $storeCode = null)
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

        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);
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

    /**
     * @param array $fixtureProduct
     *
     * @dataProvider productCreationProvider
     * @magentoApiDataFixture Magento/Store/_files/fixture_store_with_catalogsearch_index.php
     */
    public function testCreateAllStoreCode($fixtureProduct)
    {
        $response = $this->saveProduct($fixtureProduct, 'all');
        $this->assertArrayHasKey(ProductInterface::SKU, $response);

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = \Magento\TestFramework\ObjectManager::getInstance()->get(
            'Magento\Store\Model\StoreManagerInterface'
        );

        foreach ($storeManager->getStores(true) as $store) {
            $code = $store->getCode();
            if ($code === Store::ADMIN_CODE) {
                continue;
            }
            $this->assertArrayHasKey(
                ProductInterface::SKU,
                $this->getProduct($fixtureProduct[ProductInterface::SKU], $code)
            );
        }
        $this->deleteProduct($fixtureProduct[ProductInterface::SKU]);
    }

    public function testCreateInvalidPriceFormat()
    {
        $this->_markTestAsRestOnly("In case of SOAP type casting is handled by PHP SoapServer, no need to test it");
        $expectedMessage = 'Error occurred during "price" processing. '
            . 'Invalid type for value: "invalid_format". Expected Type: "float".';

        try {
            $this->saveProduct(['name' => 'simple', 'price' => 'invalid_format', 'sku' => 'simple']);
            $this->fail("Expected exception was not raised");
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    /**
     * @param array $fixtureProduct
     *
     * @dataProvider productCreationProvider
     * @magentoApiDataFixture Magento/Store/_files/fixture_store_with_catalogsearch_index.php
     */
    public function testDeleteAllStoreCode($fixtureProduct)
    {
        $sku = $fixtureProduct[ProductInterface::SKU];
        $this->saveProduct($fixtureProduct);
        $this->setExpectedException('Exception', 'Requested product doesn\'t exist');

        // Delete all with 'all' store code
        $this->deleteProduct($sku, 'all');
        $this->getProduct($sku);
    }

    public function testProductLinks()
    {
        // Create simple product
        $productData = [
            ProductInterface::SKU => "product_simple_500",
            ProductInterface::NAME => "Product Simple 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 100,
            ProductInterface::STATUS => 1,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            ProductInterface::EXTENSION_ATTRIBUTES_KEY => [
                'stock_item' => $this->getStockItemData()
            ]
        ];

        $this->saveProduct($productData);

        $productLinkData = ["sku" => "product_simple_with_related_500", "link_type" => "related",
                            "linked_product_sku" => "product_simple_500", "linked_product_type" => "simple",
                            "position" => 0, "extension_attributes" => []];
        $productWithRelatedData =  [
            ProductInterface::SKU => "product_simple_with_related_500",
            ProductInterface::NAME => "Product Simple with Related 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 100,
            ProductInterface::STATUS => 1,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => [$productLinkData]
        ];

        $this->saveProduct($productWithRelatedData);
        $response = $this->getProduct("product_simple_with_related_500");

        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals(1, count($links));
        $this->assertEquals($productLinkData, $links[0]);

        // update link information
        $productLinkData = ["sku" => "product_simple_with_related_500", "link_type" => "upsell",
                            "linked_product_sku" => "product_simple_500", "linked_product_type" => "simple",
                            "position" => 0, "extension_attributes" => []];
        $productWithUpsellData =  [
            ProductInterface::SKU => "product_simple_with_related_500",
            ProductInterface::NAME => "Product Simple with Related 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 100,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => [$productLinkData]
        ];

        $this->saveProduct($productWithUpsellData);
        $response = $this->getProduct("product_simple_with_related_500");

        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals(1, count($links));
        $this->assertEquals($productLinkData, $links[0]);

        // Remove link
        $productWithNoLinkData =  [
            ProductInterface::SKU => "product_simple_with_related_500",
            ProductInterface::NAME => "Product Simple with Related 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 100,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => []
        ];

        $this->saveProduct($productWithNoLinkData);
        $response = $this->getProduct("product_simple_with_related_500");
        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals([], $links);

        $this->deleteProduct("product_simple_500");
        $this->deleteProduct("product_simple_with_related_500");
    }

    /**
     * @param string $productSku
     * @return array
     */
    protected function getOptionsData($productSku)
    {
        return [
            [
                "product_sku" => $productSku,
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
                "product_sku" => $productSku,
                "title" => "CheckboxOption",
                "type" => "checkbox",
                "sort_order" => 1,
                "is_require" => false,
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
        $optionsDataInput = $this->getOptionsData($productData['sku']);
        $productData['options'] = $optionsDataInput;
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));
        $this->assertEquals(1, count($options[0]['values']));
        $this->assertEquals(1, count($options[1]['values']));

        //update the product options, adding a value to option 1, delete an option and create a new option
        $options[0]['values'][] = [
            "title" => "Value2",
            "price" => 6,
            "price_type" => "fixed",
            'sort_order' => 3,
        ];
        $options[1] = [
            "product_sku" => $productData['sku'],
            "title" => "DropdownOption2",
            "type" => "drop_down",
            "sort_order" => 3,
            "is_require" => false,
            "values" => [
                [
                    "title" => "Value3",
                    "price" => 7,
                    "price_type" => "fixed",
                    "sort_order" => 4,
                ],
            ],
        ];
        $response['options'] = $options;
        $response = $this->updateProduct($response);
        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));
        $this->assertEquals(2, count($options[0]['values']));
        $this->assertEquals(1, count($options[1]['values']));

        //update product without setting options field, option should not be changed
        unset($response['options']);
        $this->updateProduct($response);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $this->assertArrayHasKey('options', $response);
        $options = $response['options'];
        $this->assertEquals(2, count($options));

        //update product with empty options, options should be removed
        $response['options'] = [];
        $response = $this->updateProduct($response);
        $this->assertEmpty($response['options']);

        $this->deleteProduct($productData[ProductInterface::SKU]);
    }

    public function testProductWithMediaGallery()
    {
        $testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
        $encodedImage = base64_encode(file_get_contents($testImagePath));
        //create a product with media gallery
        $filename1 = 'tiny1' . time() . '.jpg';
        $filename2 = 'tiny2' . time() . '.jpeg';
        $productData = $this->getSimpleProductData();
        $productData['media_gallery_entries'] = [
            [
                'position' => 1,
                'media_type' => 'image',
                'disabled' => true,
                'label' => 'tiny1',
                'types' => [],
                'content' => [
                    'type' => 'image/jpeg',
                    'name' => $filename1,
                    'base64_encoded_data' => $encodedImage,
                ]
            ],
            [
                'position' => 2,
                'media_type' => 'image',
                'disabled' => false,
                'label' => 'tiny2',
                'types' => ['image', 'small_image'],
                'content' => [
                    'type' => 'image/jpeg',
                    'name' => $filename2,
                    'base64_encoded_data' => $encodedImage,
                ]
            ],
        ];
        $response = $this->saveProduct($productData);
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
                'media_type' => 'image',
                'disabled' => true,
                'types' => [],
                'file' => '/t/i/' . $filename1,
            ],
            [
                'label' => 'tiny2',
                'position' => 2,
                'media_type' => 'image',
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'file' => '/t/i/' . $filename2,
            ],
        ];
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //update the product media gallery
        $response['media_gallery_entries'] = [
            [
                'id' => $id,
                'media_type' => 'image',
                'label' => 'tiny1_new_label',
                'position' => 1,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'file' => '/t/i/' . $filename1,
            ],
        ];
        $response = $this->updateProduct($response);
        $mediaGalleryEntries = $response['media_gallery_entries'];
        $this->assertEquals(1, count($mediaGalleryEntries));
        unset($mediaGalleryEntries[0]['id']);
        $expectedValue = [[
            'label' => 'tiny1_new_label',
            'media_type' => 'image',
            'position' => 1,
            'disabled' => false,
            'types' => ['image', 'small_image'],
            'file' => '/t/i/' . $filename1,
        ]];
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //don't set the media_gallery_entries field, existing entry should not be touched
        unset($response['media_gallery_entries']);
        $response = $this->updateProduct($response);
        $mediaGalleryEntries = $response['media_gallery_entries'];
        $this->assertEquals(1, count($mediaGalleryEntries));
        unset($mediaGalleryEntries[0]['id']);
        $this->assertEquals($expectedValue, $mediaGalleryEntries);
        //pass empty array, delete all existing media gallery entries
        $response['media_gallery_entries'] = [];
        $response = $this->updateProduct($response);
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

    /**
     * @param array $product
     * @return array|bool|float|int|string
     */
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
     * @param $customAttributes
     * @return array
     */
    protected function convertCustomAttributesToAssociativeArray($customAttributes)
    {
        $converted = [];
        foreach ($customAttributes as $customAttribute) {
            $converted[$customAttribute['attribute_code']] = $customAttribute['value'];
        }
        return $converted;
    }

    /**
     * @param $data
     * @return array
     */
    protected function convertAssociativeArrayToCustomAttributes($data)
    {
        $customAttributes = [];
        foreach ($data as $attributeCode => $attributeValue) {
            $customAttributes[] = ['attribute_code' => $attributeCode, 'value' => $attributeValue];
        }
        return $customAttributes;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testEavAttributes()
    {
        $response = $this->getProduct('simple');

        $this->assertNotEmpty($response['custom_attributes']);
        $customAttributesData = $this->convertCustomAttributesToAssociativeArray($response['custom_attributes']);
        $this->assertNotTrue(isset($customAttributesData['name']));
        $this->assertNotTrue(isset($customAttributesData['tier_price']));

        //Set description
        $descriptionValue = "new description";
        $customAttributesData['description'] = $descriptionValue;
        $response['custom_attributes'] = $this->convertAssociativeArrayToCustomAttributes($customAttributesData);

        $response = $this->updateProduct($response);
        $this->assertNotEmpty($response['custom_attributes']);

        $customAttributesData = $this->convertCustomAttributesToAssociativeArray($response['custom_attributes']);
        $this->assertTrue(isset($customAttributesData['description']));
        $this->assertEquals($descriptionValue, $customAttributesData['description']);

        $this->deleteProduct('simple');
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
     * @param string|null $storeCode
     * @return mixed
     */
    protected function saveProduct($product, $storeCode = null)
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
        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
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

    public function testTierPrices()
    {
        // create a product with tier prices
        $custGroup1 = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        $custGroup2 = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
        $productData = $this->getSimpleProductData();
        $productData[self::KEY_TIER_PRICES] = [
            [
                'customer_group_id' => $custGroup1,
                'value' => 3.14,
                'qty' => 5,
            ],
            [
                'customer_group_id' => $custGroup2,
                'value' => 3.45,
                'qty' => 10,
            ]
        ];
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "CREATE: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "CREATE: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.14, $tierPrices[0]['value']);
        $this->assertEquals(5, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(3.45, $tierPrices[1]['value']);
        $this->assertEquals(10, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup2, $tierPrices[1]['customer_group_id']);

        // update the product's tier prices: update 1st tier price, (delete the 2nd tier price), add a new one
        $custGroup3 = 1;
        $tierPrices[0]['value'] = 3.33;
        $tierPrices[0]['qty'] = 6;
        $tierPrices[1] = [
            'customer_group_id' => $custGroup3,
            'value' => 2.10,
            'qty' => 12,
        ];
        $response[self::KEY_TIER_PRICES] = $tierPrices;
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "UPDATE 1: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "UPDATE 1: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.33, $tierPrices[0]['value']);
        $this->assertEquals(6, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $tierPrices[1]['value']);
        $this->assertEquals(12, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup3, $tierPrices[1]['customer_group_id']);

        // update the product without any mention of tier prices; no change expected for tier pricing
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        unset($response[self::KEY_TIER_PRICES]);
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "UPDATE 2: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "UPDATE 2: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.33, $tierPrices[0]['value']);
        $this->assertEquals(6, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $tierPrices[1]['value']);
        $this->assertEquals(12, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup3, $tierPrices[1]['customer_group_id']);

        // update the product with empty tier prices; expect to have the existing tier prices removed
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $response[self::KEY_TIER_PRICES] = [];
        $response = $this->updateProduct($response);
        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response, "expected to have the 'tier_prices' key");
        $this->assertEmpty($response[self::KEY_TIER_PRICES], "expected to have an empty array of 'tier_prices'");

        // delete the product with tier prices; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }

    /**
     * @return array
     */
    private function getStockItemData()
    {
        return [
            StockItemInterface::IS_IN_STOCK => 1,
            StockItemInterface::QTY => 100500,
            StockItemInterface::IS_QTY_DECIMAL => 1,
            StockItemInterface::SHOW_DEFAULT_NOTIFICATION_MESSAGE => 0,
            StockItemInterface::USE_CONFIG_MIN_QTY => 0,
            StockItemInterface::USE_CONFIG_MIN_SALE_QTY => 0,
            StockItemInterface::MIN_QTY => 1,
            StockItemInterface::MIN_SALE_QTY => 1,
            StockItemInterface::MAX_SALE_QTY => 100,
            StockItemInterface::USE_CONFIG_MAX_SALE_QTY => 0,
            StockItemInterface::USE_CONFIG_BACKORDERS => 0,
            StockItemInterface::BACKORDERS => 0,
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY => 0,
            StockItemInterface::NOTIFY_STOCK_QTY => 0,
            StockItemInterface::USE_CONFIG_QTY_INCREMENTS => 0,
            StockItemInterface::QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_ENABLE_QTY_INC => 0,
            StockItemInterface::ENABLE_QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_MANAGE_STOCK => 1,
            StockItemInterface::MANAGE_STOCK => 1,
            StockItemInterface::LOW_STOCK_DATE => null,
            StockItemInterface::IS_DECIMAL_DIVIDED => 0,
            StockItemInterface::STOCK_STATUS_CHANGED_AUTO => 0,
        ];
    }

    public function testSpecialPrice()
    {
        $productData = $this->getSimpleProductData();
        $productData['custom_attributes'] = [
            ['attribute_code' => self::KEY_SPECIAL_PRICE, 'value' => '1']
        ];
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $customAttributes = $response['custom_attributes'];
        $this->assertNotEmpty($customAttributes);
        $missingAttributes = ['news_from_date', 'custom_design_from'];
        $expectedAttribute = ['special_price', 'special_from_date'];
        $attributeCodes = array_column($customAttributes, 'attribute_code');
        $this->assertEquals(0, count(array_intersect($attributeCodes, $missingAttributes)));
        $this->assertEquals(2, count(array_intersect($attributeCodes, $expectedAttribute)));
    }
}
