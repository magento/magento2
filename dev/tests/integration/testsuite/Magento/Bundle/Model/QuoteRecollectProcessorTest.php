<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for \Magento\Bundle\Model\QuoteRecollectProcessor class.
 */
class QuoteRecollectProcessorTest extends TestCase
{
    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PrepareBundleLinks
     */
    private $prepareBundleLinks;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
    }

    /**
     * Tests that quote marked to recollect after bundle product options or selections changed.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/quote_with_fixed_bundle_product.php
     * @dataProvider getBundleOptionsDataProvider
     * @param array $optionsData
     * @param array $selectionsData
     * @param string $expectedTriggerRecollect
     * @return void
     */
    public function testMarkQuoteRecollectAfterChangeBundleOptions(
        array $optionsData,
        array $selectionsData,
        string $expectedTriggerRecollect
    ): void {
        $quote = $this->getQuoteByReservedOrderId->execute('test_cart_with_fixed_bundle');
        $this->assertFalse((bool)$quote->getTriggerRecollect());
        $this->updateBundleProduct('fixed_bundle_product_without_discounts', $optionsData, $selectionsData);
        $updatedQuote = $this->getQuoteByReservedOrderId->execute('test_cart_with_fixed_bundle');
        $this->assertEquals($expectedTriggerRecollect, $updatedQuote->getTriggerRecollect());
    }

    /**
     * @return array
     */
    public static function getBundleOptionsDataProvider(): array
    {
        return [
            'product option changed' => [
                [
                    [
                        'title' => 'Option 1',
                        'default_title' => 'Option 1',
                        'type' => 'radio',
                        'required' => 0,
                        'delete' => '',
                    ],
                ],
                [
                    [
                        'sku' => 'simple1',
                        'selection_qty' => 1,
                        'selection_price_value' => 10,
                        'selection_price_type' => 0,
                        'selection_can_change_qty' => 1,
                    ],
                ],
                '1',
            ],
            'product link changed' => [
                [
                    [
                        'title' => 'Option 1',
                        'default_title' => 'Option 1',
                        'type' => 'radio',
                        'required' => 1,
                        'delete' => '',
                    ],
                ],
                [
                    [
                        'sku' => 'simple1',
                        'selection_qty' => 2,
                        'selection_price_value' => 15,
                        'selection_price_type' => 1,
                        'selection_can_change_qty' => 0,
                    ],
                ],
                '1',
            ],
        ];
    }

    /**
     * Updates bundle product options and selections.
     *
     * @param string $sku
     * @param array $optionsData
     * @param array $selectionsData
     * @return void
     */
    private function updateBundleProduct(string $sku, array $optionsData, array $selectionsData): void
    {
        $product = $this->productRepository->get($sku);
        $option = current($product->getExtensionAttributes()->getBundleProductOptions());
        $optionsData[0]['option_id'] = $option->getId();
        $updatedProduct = $this->prepareBundleLinks->execute($product, $optionsData, [$selectionsData]);
        $this->productRepository->save($updatedProduct);
    }
}
