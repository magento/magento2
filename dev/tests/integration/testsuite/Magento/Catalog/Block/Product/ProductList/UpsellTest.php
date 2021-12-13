<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ProductList;

/**
 * Check the correct behavior of up-sell products on the product view page
 *
 * @see \Magento\Catalog\Block\Product\ProductList\Upsell
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class UpsellTest extends AbstractLinksTest
{
    /** @var Upsell */
    protected $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Upsell::class);
        $this->linkType = 'upsell';
    }

    /**
     * Checks for a up-sell product when block code is generated
     *
     * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
     * @return void
     */
    public function testAll(): void
    {
        $this->product = $this->productRepository->get('simple_with_upsell');
        $this->block->setProduct($this->product);
        $this->prepareBlock();
        $html = $this->block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Simple Up Sell', $html);
        $this->assertCount(1, $this->block->getItems());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
     * @return void
     */
    public function testGetIdentities(): void
    {
        $upsellProduct = $this->productRepository->get('simple');
        $this->product = $this->productRepository->get('simple_with_upsell');
        $this->block->setProduct($this->product);
        $this->prepareBlock();
        $expectedTags = ['cat_p_' . $upsellProduct->getId(), 'cat_p'];
        $tags = $this->block->getIdentities();
        $this->assertEquals($expectedTags, $tags);
    }

    /**
     * Test the display of up-sell products in the block
     *
     * @dataProvider displayLinkedProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @param array $data
     * @return void
     */
    public function testDisplayUpsellProducts(array $data): void
    {
        $this->updateProducts($data['updateProducts']);
        $this->linkProducts('simple', $this->existingProducts);
        $this->product = $this->productRepository->get('simple');
        $this->block->setProduct($this->product);
        $items = $this->block->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected up-sell products do not match actual up-sell products!'
        );
    }

    /**
     * Test the position of up-sell products in the block
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @return void
     */
    public function testPositionUpsellProducts(): void
    {
        $data = $this->getPositionData();
        $this->linkProducts('simple', $data['productLinks']);
        $this->product = $this->productRepository->get('simple');
        $this->block->setProduct($this->product);
        $items = $this->block->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected up-sell products do not match actual up-sell products!'
        );
    }

    /**
     * Test the display of up-sell products in the block on different websites
     *
     * @dataProvider multipleWebsitesLinkedProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoAppIsolation enabled
     * @param array $data
     * @return void
     */
    public function testMultipleWebsitesUpsellProducts(array $data): void
    {
        $this->updateProducts($this->prepareProductsWebsiteIds());
        $productLinks = array_replace_recursive($this->existingProducts, $data['productLinks']);
        $this->linkProducts('simple-1', $productLinks);
        $this->product = $this->productRepository->get(
            'simple-1',
            false,
            $this->storeManager->getStore($data['storeCode'])->getId()
        );
        $this->block->setProduct($this->product);
        $items = $this->block->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected up-sell products do not match actual up-sell products!'
        );
    }
}
