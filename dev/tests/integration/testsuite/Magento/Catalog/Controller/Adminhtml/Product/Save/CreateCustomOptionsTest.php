<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Base test cases for product custom options with type "field".
 * Option add via dispatch product controller action save with options data in POST data.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
 */
class CreateCustomOptionsTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $productSku = 'simple';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->optionRepository = $this->_objectManager->create(ProductCustomOptionRepositoryInterface::class);
    }

    /**
     * Test add to product custom option with type "field".
     *
     * @dataProvider productWithNewOptionsDataProvider
     *
     * @param array $productPostData
     */
    public function testSaveCustomOptionWithTypeField(array $productPostData): void
    {
        $this->getRequest()->setPostValue($productPostData);
        $product = $this->productRepository->get($this->productSku);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertSessionMessages(
            $this->containsEqual('You saved the product.'),
            MessageInterface::TYPE_SUCCESS
        );
        $productOptions = $this->optionRepository->getProductOptions($product);
        $this->assertCount(2, $productOptions);
        foreach ($productOptions as $customOption) {
            $postOptionData = $productPostData['product']['options'][$customOption->getTitle()] ?? null;
            $this->assertNotNull($postOptionData);
            $this->assertEquals($postOptionData['title'], $customOption->getTitle());
            $this->assertEquals($postOptionData['type'], $customOption->getType());
            $this->assertEquals($postOptionData['is_require'], $customOption->getIsRequire());
            $this->assertEquals($postOptionData['sku'], $customOption->getSku());
            $this->assertEquals($postOptionData['price'], $customOption->getPrice());
            $this->assertEquals($postOptionData['price_type'], $customOption->getPriceType());
            $maxCharacters = $postOptionData['max_characters'] ?? 0;
            $this->assertEquals($maxCharacters, $customOption->getMaxCharacters());
        }
    }

    /**
     * Return all data for add option to product for all cases.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function productWithNewOptionsDataProvider(): array
    {
        return [
            'required_options' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 1,
                                'sku' => 'test-option-title-1',
                                'max_characters' => 50,
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 1,
                                'sku' => 'test-option-title-2',
                                'max_characters' => 50,
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                        ],
                    ],
                ],
            ],
            'not_required_options' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 0,
                                'sku' => 'test-option-title-1',
                                'max_characters' => 50,
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 0,
                                'sku' => 'test-option-title-2',
                                'max_characters' => 50,
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                        ],
                    ],
                ],
            ],
            'options_with_fixed_price' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 1,
                                'sku' => 'test-option-title-1',
                                'max_characters' => 50,
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 1,
                                'sku' => 'test-option-title-2',
                                'max_characters' => 50,
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'percent',
                            ],
                        ],
                    ],
                ],
            ],
            'options_with_percent_price' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 1,
                                'sku' => 'test-option-title-1',
                                'max_characters' => 50,
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 1,
                                'sku' => 'test-option-title-2',
                                'max_characters' => 50,
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 20,
                                'price_type' => 'percent',
                            ],
                        ],
                    ],
                ],
            ],
            'options_with_max_charters_configuration' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 1,
                                'sku' => 'test-option-title-1',
                                'max_characters' => 30,
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 1,
                                'sku' => 'test-option-title-2',
                                'max_characters' => 50,
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                        ],
                    ],
                ],
            ],
            'options_without_max_charters_configuration' => [
                [
                    'product' => [
                        'options' => [
                            'Test option title 1' => [
                                'record_id' => 0,
                                'sort_order' => 1,
                                'is_require' => 1,
                                'sku' => 'test-option-title-1',
                                'title' => 'Test option title 1',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                            'Test option title 2' => [
                                'record_id' => 1,
                                'sort_order' => 2,
                                'is_require' => 1,
                                'sku' => 'test-option-title-2',
                                'title' => 'Test option title 2',
                                'type' => 'field',
                                'price' => 10,
                                'price_type' => 'fixed',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
