<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test for Magento\Quote\Api\GuestCartItemRepositoryInterface.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestCartItemRepositoryTest extends WebapiAbstract
{
    public const SERVICE_NAME = 'quoteGuestCartItemRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test quote items
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testGetList()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $output = [];
        /** @var  \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($quote->getAllItems() as $item) {
            //Set masked Cart ID
            $item->setQuoteId($cartId);
            $data = [
                'item_id' => $item->getItemId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'qty' => $item->getQty(),
                'product_type' => $item->getProductType(),
                'quote_id' => $item->getQuoteId()
            ];

            $output[] = $data;
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($output, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testAddItem()
    {
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class)->load(2);
        $productSku = $product->getSku();
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = [
            'cartItem' => [
                'sku' => $productSku,
                'qty' => 7,
            ],
        ];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem']['quote_id'] = $cartId;
        }

        $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($quote->hasProductId(2));
        $this->assertEquals(7, $quote->getItemByProduct($product)->getQty());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     */
    public function testRemoveItem()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple_one');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "itemId" => $itemId,
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertFalse($quote->hasProductId($productId));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     * @param array $stockData
     * @param string|null $errorMessage
     * @dataProvider updateItemDataProvider
     */
    public function testUpdateItem(array $stockData, ?string $errorMessage = null)
    {
        $this->updateStockData('simple_one', $stockData);
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple_one');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['cartItem']['qty'] = 5;
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem'] += [
                'quote_id' => $cartId,
                'itemId' => $itemId,
            ];
        }
        if ($errorMessage) {
            $this->expectExceptionMessage($errorMessage);
        }
        $this->_webApiCall($serviceInfo, $requestData);
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        $this->assertEquals(5, $item->getQty());
        $this->assertEquals($itemId, $item->getItemId());
    }

    /**
     * Verifies that store id for quote and quote item is being changed accordingly to the requested store code
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_items_saved.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testUpdateItemWithChangingStoreId()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        $cartId = $quoteIdMask->getMaskedId();

        $product = $this->objectManager->create(Product::class);
        $productId = $product->getIdBySku('simple');
        $product->load($productId);
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData['cartItem']['qty'] = 5;
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartItem'] += [
                'quote_id' => $cartId,
                'itemId' => $itemId,
            ];
        }
        $this->_webApiCall($serviceInfo, $requestData, null, 'fixture_second_store');
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_item_with_items', 'reserved_order_id');
        $this->assertTrue($quote->hasProductId(1));
        $item = $quote->getItemByProduct($product);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeId = $storeManager->getStore('fixture_second_store')
            ->getId();
        $this->assertEquals($storeId, $quote->getStoreId());
        $this->assertEquals($storeId, $item->getStoreId());
    }

    /**
     * @return array
     */
    public static function updateItemDataProvider(): array
    {
        return [
            [
                []
            ],
            [
                [
                    'qty' => 0,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                ]
            ],
            [
                [
                    'qty' => 0,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_NO,
                ],
                'There are no source items with the in stock status'
            ],
            [
                [
                    'qty' => 2,
                    'is_in_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'use_config_backorders' => 0,
                    'backorders' => Stock::BACKORDERS_NO,
                ],
                'Not enough items for sale'
            ]
        ];
    }

    #[
        DataProvider('addItemWithFileCustomOptionDataProvider'),
        DataFixture(
            ProductFixture::class,
            [
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                        'title' => 'file_opt',
                        'file_extension' => 'png, jpg, gif',
                        'image_size_x' => 300,
                        'image_size_y' => 300,
                    ]
                ]
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                        'title' => 'file_opt',
                        'file_extension' => 'png, jpg, gif',
                        'image_size_x' => 300,
                        'image_size_y' => 300,
                        'is_require' => false,
                    ]
                ]
            ],
            as: 'product2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddItemWithFileCustomOption(
        string $product,
        array $customOptions,
        array $expectedCustomOptions = [],
        array $expectFilenames = []
    ): void {
        $this->_markTestAsRestOnly();
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get($product);
        $cart = $fixtures->get('cart');

        $item = $this->addProductToCart((int)$cart->getId(), $product->getSku(), [
            'product_option' => [
                'extension_attributes' => [
                    'custom_options' => $this->prepareCustomOptions($product->getOptions(), $customOptions)
                ]
            ]
        ]);
        $this->assertNotEmpty($item);
        $optionsIds = [];
        foreach ($product->getOptions() as $option) {
            $optionsIds[$option->getTitle()] = $option->getOptionId();
        }
        $this->assertEqualsCanonicalizing(
            array_map(fn ($option) => $optionsIds[$option] ?? $option, $expectedCustomOptions),
            array_column($item['product_option']['extension_attributes']['custom_options'] ?? [], 'option_id')
        );
        $fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $directory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        foreach ($expectFilenames as $expectFilename) {
            $this->assertTrue($directory->isExist($expectFilename));
        }
    }

    #[
        DataProvider('addItemValidationWithFileCustomOptionDataProvider'),
        DataFixture(
            ProductFixture::class,
            [
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                        'title' => 'file_opt',
                        'file_extension' => 'png, jpg, gif',
                        'image_size_x' => 300,
                        'image_size_y' => 300,
                    ],
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'title' => 'field_opt',
                        'is_require' => false,
                    ]
                ]
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'title' => 'field_opt',
                        'is_require' => false,
                    ]
                ]
            ],
            as: 'product2'
        ),
        DataFixture(ProductFixture::class, as: 'product3'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddItemValidationWithFileCustomOption(
        string $product,
        array $customOptions,
        string $expectedException
    ): void {
        $this->_markTestAsRestOnly();
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get($product);
        $cart = $fixtures->get('cart');
        $actualException = '';
        try {
            $this->addProductToCart((int)$cart->getId(), $product->getSku(), [
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $this->prepareCustomOptions($product->getOptions(), $customOptions)
                    ]
                ]
            ]);
        } catch (\Throwable $exception) {
            $actualException = $exception->getMessage();
            $json = json_decode($actualException, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['message'], $json['parameters'])) {
                $actualException = (string) (new Phrase($json['message'], $json['parameters']));
            }
        }

        $this->assertStringContainsString(
            $expectedException,
            $actualException,
            'Failed asserting that exception with message: ' . $expectedException . ' was thrown.'
        );
        $fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $directory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        foreach ($customOptions as $customOption) {
            if (!empty($customOption['extension_attributes']['file_info'])) {
                $fileInfo = $customOption['extension_attributes']['file_info'];
                $filename = $fileInfo['name'];
                $path = 'custom_options/quote/' . $filename[0] . '/' . $filename[1] . '/' . $filename;
                $this->assertFalse($directory->isExist($path), 'File ' . $filename . ' should not exist.');
            }
        }
    }

    public static function addItemWithFileCustomOptionDataProvider(): array
    {
        return [
            'required file option with valid image' => [
                'product1',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'this value should not matter',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [100, 100, 'image/jpeg'],
                                'type' => 'image/jpeg',
                                'name' => 'valid_file1.jpg'
                            ]
                        ]
                    ]
                ],
                ['file_opt'],
                ['custom_options/quote/v/a/valid_file1.jpg']
            ],
            'optional file option missing extension_attributes' => [
                'product2',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'this value should not matter',
                    ]
                ],
            ],
            'optional file option with file_info NULL' => [
                'product2',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'this value should not matter',
                        'extension_attributes' => [
                            'file_info' => null
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * @return array[]
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public static function addItemValidationWithFileCustomOptionDataProvider(): array
    {
        return [
            'file_info is null' => [
                'product1',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'file',
                        'extension_attributes' => [
                            'file_info' => null
                        ]
                    ]
                ],
                'The product\'s required option(s) weren\'t entered. Make sure the options are entered and try again.'
            ],
            'invalid extension' => [
                'product1',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'file',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [100, 100, 'image/jpeg'],
                                'type' => 'image/jpeg',
                                'name' => 'invalid_file1.html'
                            ]
                        ]
                    ]
                ],
                'The file \'invalid_file1.html\' for \'file_opt\' has an invalid extension.'
            ],
            'invalid file name' => [
                'product1',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'file',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [100, 100, 'image/jpeg'],
                                'type' => 'image/jpeg',
                                'name' => 'path/invalid_file2.jpeg'
                            ]
                        ]
                    ]
                ],
                'Provided image name contains forbidden characters.'
            ],
            'image is too big' => [
                'product1',
                [
                    [
                        'option_id' => 'file_opt',
                        'option_value' => 'file',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [1000, 100, 'image/jpeg'],
                                'type' => 'image/jpeg',
                                'name' => 'invalid_file3.jpg'
                            ]
                        ]
                    ]
                ],
                'The maximum allowed image size for \'file_opt\' is 300x300 px.'
            ],
            'option_id provided is not a file' => [
                'product2',
                [
                    [
                        'option_id' => 'field_opt',
                        'option_value' => 'file',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [100, 100, 'image/jpeg'],
                                'type' => 'image/jpeg',
                                'name' => 'valid_file2.jpeg'
                            ]
                        ]
                    ]
                ],
                // no exception will be thrown and the file will not be saved
                ''
            ],
            'option_id provided does not exist' => [
                'product3',
                [
                    [
                        'option_id' => '1',
                        'option_value' => 'test',
                        'extension_attributes' => [
                            'file_info' => [
                                'base64_encoded_data' => [100, 100, 'image/jpeg'],
                                'type' => 'image/gif',
                                'name' => 'invalid_file5.html'
                            ]
                        ]
                    ]
                ],
                'No such entity with option_id = 1'
            ]
        ];
    }

    private static function generateImage($width, $height, $type): string
    {
        ob_start();
        $image = imagecreatetruecolor($width, $height);
        switch ($type) {
            case 'image/jpeg':
                imagejpeg($image);
                break;
            case 'image/png':
                imagepng($image);
                break;
            case 'image/gif':
                imagegif($image);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported image type');
        }
        $content = base64_encode(ob_get_clean());
        return $content;
    }

    private function addProductToCart(int $cartId, string $sku, array $data): array
    {
        $maskedQuoteId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class)->execute($cartId);
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $maskedQuoteId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $requestData = [
            'cartItem' => [
                'quote_id' => $maskedQuoteId,
                'sku' => $sku,
                'qty' => 1,
                ...$data
            ]
        ];

        return $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
    }

    private static function prepareCustomOptions(array $productOptions, array $customOptions): array
    {
        $optionsIds = [];
        foreach ($productOptions as $option) {
            $optionsIds[$option->getTitle()] = $option->getOptionId();
        }
        $result = [];
        foreach ($customOptions as $customOption) {
            if (isset($optionsIds[$customOption['option_id']])) {
                $customOption['option_id'] = $optionsIds[$customOption['option_id']];
            }
            if (is_array($customOption['extension_attributes']['file_info']['base64_encoded_data'] ?? null)) {
                $customOption['extension_attributes']['file_info']['base64_encoded_data'] = self::generateImage(
                    ...$customOption['extension_attributes']['file_info']['base64_encoded_data']
                );
            }
            $result[] = $customOption;
        }
        return $result;
    }

    /**
     * Update product stock
     *
     * @param string $sku
     * @param array $stockData
     * @return void
     */
    private function updateStockData(string $sku, array $stockData): void
    {
        if ($stockData) {
            /** @var $stockRegistry StockRegistryInterface */
            $stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
            $stockItem = $stockRegistry->getStockItemBySku($sku);
            $stockItem->addData($stockData);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        }
    }
}
