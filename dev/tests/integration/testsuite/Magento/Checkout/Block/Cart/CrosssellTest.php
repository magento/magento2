<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Block\Cart;

use Magento\Catalog\Block\Product\ProductList\AbstractLinksTest;
use Magento\Catalog\ViewModel\Product\Listing\PreparePostData;
use Magento\Checkout\Model\Session;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\TestFramework\Store\ExecuteInStoreContext;

/**
 * Check the correct behavior of cross-sell products in the shopping cart
 *
 * @see \Magento\Checkout\Block\Cart\Crosssell
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class CrosssellTest extends AbstractLinksTest
{
    private const MAX_ITEM_COUNT = 4;

    /** @var Session */
    private $checkoutSession;

    /** @var string */
    private $addToCartButtonXpath = "//div[contains(@class, 'actions-primary')]/button[@type='button']";

    /** @var string */
    private $addToCartSubmitXpath = "//div[contains(@class, 'actions-primary')]"
    . "/form[@data-product-sku='%s']/button[@type='submit']";

    /** @var string */
    private $addToLinksXpath = "//div[contains(@class, 'actions-secondary')]";

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Crosssell::class);
        $this->linkType = 'crosssell';
        $this->titleName = (string)__('More Choices:');
        $this->checkoutSession = $this->objectManager->get(Session::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * Checks for a simple cross-sell product when block code is generated
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testSimpleCrosssellProduct(): void
    {
        $relatedProduct = $this->productRepository->get('simple-1');
        $this->linkProducts('simple', ['simple-1' => ['position' => 2]]);
        $this->setCheckoutSessionQuote('test_order_with_simple_product_without_address');
        $this->prepareBlock();
        $html = $this->block->toHtml();

        $this->assertNotEmpty($html);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->titleXpath, $this->linkType, $this->titleName), $html),
            'Expected title is incorrect or missing!'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->addToCartSubmitXpath, $relatedProduct->getSku()), $html),
            'Expected add to cart button is incorrect or missing!'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->addToLinksXpath, $html),
            'Expected add to links is incorrect or missing!'
        );
    }

    /**
     * Checks for a cross-sell product with required option when block code is generated
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual_with_options.php
     * @return void
     */
    public function testCrosssellProductWithRequiredOption(): void
    {
        $this->linkProducts('simple', ['virtual' => ['position' => 1]]);
        $this->setCheckoutSessionQuote('test_order_with_simple_product_without_address');
        $this->prepareBlock();
        $html = $this->block->toHtml();

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->addToCartButtonXpath, $html),
            'Expected add to cart button is incorrect or missing!'
        );
    }

    /**
     * Test the display of cross-sell products in the block
     *
     * @dataProvider displayLinkedProductsProvider
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @param array $data
     * @return void
     */
    public function testDisplayCrosssellProducts(array $data): void
    {
        $this->updateProducts($data['updateProducts']);
        $this->linkProducts('simple', $this->existingProducts);
        $items = $this->getBlockItems('test_order_with_simple_product_without_address');

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected cross-sell products do not match actual cross-sell products!'
        );
    }

    /**
     * Test the position and max count of cross-sell products in the block
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testPositionCrosssellProducts(): void
    {
        $positionData = array_merge_recursive(
            $this->getPositionData(),
            [
                'productLinks' => [
                    'simple-1' => ['position' => 5],
                    'simple2' => ['position' => 4],
                ],
                'expectedProductLinks' => [
                    'simple2',
                ],
            ]
        );
        $this->linkProducts('simple', $positionData['productLinks']);
        $items = $this->getBlockItems('test_order_with_simple_product_without_address');

        $this->assertCount(
            self::MAX_ITEM_COUNT,
            $items,
            'Expected quantity of cross-sell products do not match the actual quantity!'
        );
        $this->assertEquals(
            $positionData['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected cross-sell products do not match actual cross-sell products!'
        );
    }

    /**
     * Test the position and max count of cross-sell products in the block
     * when set last added product in checkout session
     *
     * @dataProvider positionWithLastAddedProductProvider
     * @magentoDataFixture Magento/Sales/_files/quote_with_multiple_products.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @param array $positionData
     * @param array $expectedProductLinks
     * @return void
     */
    public function testPositionCrosssellProductsWithLastAddedProduct(
        array $positionData,
        array $expectedProductLinks
    ): void {
        foreach ($positionData as $sku => $productLinks) {
            $this->linkProducts($sku, $productLinks);
        }
        $this->checkoutSession->setLastAddedProductId($this->productRepository->get('simple-tableRate-1')->getId());
        $items = $this->getBlockItems('tableRate');

        $this->assertCount(
            self::MAX_ITEM_COUNT,
            $items,
            'Expected quantity of cross-sell products do not match the actual quantity!'
        );
        $this->assertEquals(
            $expectedProductLinks,
            $this->getActualLinks($items),
            'Expected cross-sell products do not match actual cross-sell products!'
        );
    }

    /**
     * Provide test data to verify the position of linked products of the last added product.
     *
     * @return array
     */
    public function positionWithLastAddedProductProvider(): array
    {
        return [
            'less_four_linked_products_to_last_added_product' => [
                'positionData' => [
                    'simple-tableRate-1' => [
                        'simple-249' => ['position' => 2],
                        'simple-156' => ['position' => 1],
                    ],
                    'simple-tableRate-2' => [
                        'simple-1' => ['position' => 2],
                        'simple2' => ['position' => 1],
                        'wrong-simple' => ['position' => 3],
                    ],
                ],
                'expectedProductLinks' => [
                    'simple-156',
                    'simple-249',
                    'simple2',
                    'simple-1',
                ],
            ],
            'four_linked_products_to_last_added_product' => [
                'positionData' => [
                    'simple-tableRate-1' => [
                        'wrong-simple' => ['position' => 3],
                        'simple-249' => ['position' => 1],
                        'simple-156' => ['position' => 2],
                        'simple2' => ['position' => 4],
                    ],
                    'simple-tableRate-2' => [
                        'simple-1' => ['position' => 1],
                    ],
                ],
                'expectedProductLinks' => [
                    'simple-249',
                    'simple-156',
                    'wrong-simple',
                    'simple2',
                ],
            ],
        ];
    }

    /**
     * Test the display of cross-sell products in the block on different websites
     *
     * @dataProvider multipleWebsitesLinkedProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_multiple_products.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @param array $data
     * @return void
     */
    public function testMultipleWebsitesCrosssellProducts(array $data): void
    {
        $this->updateProducts($this->prepareProductsWebsiteIds());
        $productLinks = array_merge($this->existingProducts, $data['productLinks']);
        $this->linkProducts('simple-tableRate-1', $productLinks);
        $items = $this->executeInStoreContext->execute($data['storeCode'], [$this, 'getBlockItems'], 'tableRate');

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected cross-sell products do not match actual cross-sell products!'
        );
    }

    /**
     * Test the invisibility of cross-sell products in the block which added to cart
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_multiple_products.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testInvisibilityCrosssellProductAddedToCart(): void
    {
        $productLinks = [
            'simple-1' => ['position' => 1],
            'simple-tableRate-2' => ['position' => 2],
        ];
        $this->linkProducts('simple-tableRate-1', $productLinks);
        $items = $this->getBlockItems('tableRate');

        $this->assertEquals(
            ['simple-1'],
            $this->getActualLinks($items),
            'Expected cross-sell products do not match actual cross-sell products!'
        );
    }

    /**
     * Get products of block when quote in checkout session
     *
     * @param string $reservedOrderId
     * @return array
     */
    public function getBlockItems(string $reservedOrderId): array
    {
        $this->setCheckoutSessionQuote($reservedOrderId);

        return $this->block->getItems();
    }

    /**
     * @inheritdoc
     */
    protected function prepareBlock(): void
    {
        parent::prepareBlock();

        $this->block->setViewModel($this->objectManager->get(PreparePostData::class));
    }

    /**
     * @inheritdoc
     */
    protected function prepareProductsWebsiteIds(): array
    {
        $productsWebsiteIds = parent::prepareProductsWebsiteIds();
        $simple = $productsWebsiteIds['simple-1'];
        unset($productsWebsiteIds['simple-1']);

        return array_merge($productsWebsiteIds, ['simple-tableRate-1' => $simple]);
    }

    /**
     * Set quoteId in checkoutSession object.
     *
     * @param string $reservedOrderId
     * @return void
     */
    private function setCheckoutSessionQuote(string $reservedOrderId): void
    {
        $this->checkoutSession->clearQuote();
        $quote = $this->objectManager->get(GetQuoteByReservedOrderId::class)->execute($reservedOrderId);
        if ($quote !== null) {
            $this->checkoutSession->setQuoteId($quote->getId());
        }
    }
}
