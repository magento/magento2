<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Laminas\File\Transfer\Adapter\Http;

#[
    DataFixture(GuestCart::class, as: 'quote'),
    DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    DataFixture(
        Product::class,
        [
            'options' => [
                [
                    'title' => 'option title',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_FILE,
                    'file_extension' => 'jpg, png, gif',
                    'values' => [
                        [
                            'title' => 'file',
                        ]
                    ]
                ]
            ]
        ],
        'product'
    )
]
class GetCartQueryForProductWithCustomOptionFile extends GraphQlAbstract
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var string
     */
    private $fileToRemove;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * Test cart query
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCartQueryForProductWithCustomOptionAsFile(): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $this->addProductInQuote();
        $query = $this->getQuery($cartId);
        $response = $this->graphQlMutation($query);
        $items = $response['cart']['itemsV2']['items'];

        $this->assertStringContainsString(
            'magento_small_image.jpg',
            $items[0]['customizable_options'][0]['values'][0]['value']
        );

        $expectedResult = [
            'customizable_options'=>[[
            'type'=>'file',
            'label' => 'option title',
            'values'=>[
                [
                'label'=>'option title',
                'value' => $items[0]['customizable_options'][0]['values'][0]['value']
                ]
            ]
            ]],
            'product' => ['name' => $product->getName(), 'sku'=>$product->getData('sku')],
            'quantity' => 1
        ];

        $this->assertResponseFields($expectedResult, $items[0]);
    }

    /**
     * Add product in cart
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addProductInQuote(): void
    {
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $productOptions = $product->getOptions();
        $fileOptionId = [];
        foreach ($productOptions as $option) {
            foreach ($option->getValues() as $value) {
                $fileOptionId[] = $value['option_id'];
            }
        }

        $this->prepareEnv($fileOptionId);
        $buyRequest = new DataObject(
            [
                'qty' => 1,
                'options' => ['files_prefix' => 'item_simple_with_custom_file_option_'],
            ]
        );
        $quote->addProduct($product, $buyRequest);
        $this->cartRepository->save($quote);
    }

    /**
     * Prepare file upload environment
     *
     * @param array $optionIds
     * @return void
     */
    private function prepareEnv(array $optionIds): void
    {
        $file = 'magento_small_image.jpg';
        $fixtureDir = realpath(__DIR__ . '/../_files/');
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($file);
        $this->fileToRemove = $filePath;
        copy($fixtureDir . DIRECTORY_SEPARATOR . $file, $filePath);

        $_FILES["item_simple_with_custom_file_option_options_$optionIds[0]_file"] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => '3046',
        ];
        $this->prepareUploaderFactoryMock();
    }

    /**
     * Prepare file upload validator mock
     *
     * @return void
     */
    private function prepareUploaderFactoryMock(): void
    {
        $uploaderMock = $this->getPreparedUploader();
        /** @var FileTransferFactory $httpFactory */
        $httpFactoryMock = $this->createPartialMock(FileTransferFactory::class, ['create']);
        $httpFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls($uploaderMock, clone $uploaderMock);
        Bootstrap::getObjectManager()->addSharedInstance($httpFactoryMock, FileTransferFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if (file_exists($this->fileToRemove)) {
            unlink($this->fileToRemove);
        }

        parent::tearDown();
    }

    /**
     * Create prepared uploader instance for test
     *
     * @return Http
     */
    private function getPreparedUploader(): Http
    {
        $uploader = new Http();
        $refObject = new \ReflectionObject($uploader);
        $validators = $refObject->getProperty('validators');
        $validators->setAccessible(true);
        $validators->setValue($uploader, []);
        $files = $refObject->getProperty('files');
        $files->setAccessible(true);
        $filesValues = $files->getValue($uploader);
        foreach (array_keys($filesValues) as $value) {
            $filesValues[$value]['validators'] = [];
        }
        $files->setValue($uploader, $filesValues);
        return $uploader;
    }

    /**
     * Create cart query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    itemsV2 {
      total_count
      items {
        ... on SimpleCartItem {
          customizable_options {
            type
            label
            values {
              label
              value
            }
          }
        }
        product {
          name
          sku
        }
        quantity
      }
    }
    prices {
      grand_total {
        value
        currency
      }
    }
  }
}
QUERY;
    }
}
