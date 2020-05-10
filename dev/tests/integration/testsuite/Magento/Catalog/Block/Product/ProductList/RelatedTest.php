<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as LinkProductCollection;

/**
 * Check the correct behavior of related products on the product view page
 *
 * @see \Magento\Catalog\Block\Product\ProductList\Related
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class RelatedTest extends AbstractLinksTest
{
    /** @var Related */
    protected $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Related::class);
        $this->linkType = 'related';
    }

    /**
     * Checks for a related product when block code is generated
     *
     * @magentoDataFixture Magento/Catalog/_files/products_related.php
     * @return void
     */
    public function testAll(): void
    {
        /** @var ProductInterface $relatedProduct */
        $relatedProduct = $this->productRepository->get('simple');
        $this->product = $this->productRepository->get('simple_with_cross');
        $this->block->setProduct($this->product);
        $this->prepareBlock();
        $html = $this->block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertStringContainsString($relatedProduct->getName(), $html);
        /* name */
        $this->assertStringContainsString('id="related-checkbox' . $relatedProduct->getId() . '"', $html);
        /* part of url */
        $this->assertInstanceOf(
            LinkProductCollection::class,
            $this->block->getItems()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_related.php
     * @return void
     */
    public function testGetIdentities(): void
    {
        /** @var ProductInterface $relatedProduct */
        $relatedProduct = $this->productRepository->get('simple');
        $this->product = $this->productRepository->get('simple_with_cross');
        $this->block->setProduct($this->product);
        $this->prepareBlock();
        $expectedTags = ['cat_p_' . $relatedProduct->getId(), 'cat_p'];
        $tags = $this->block->getIdentities();
        $this->assertEquals($expectedTags, $tags);
    }

    /**
     * Test the display of related products in the block
     *
     * @dataProvider displayLinkedProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @param array $data
     * @return void
     */
    public function testDisplayRelatedProducts(array $data): void
    {
        $this->updateProducts($data['updateProducts']);
        $this->linkProducts('simple', $this->existingProducts);
        $this->product = $this->productRepository->get('simple');
        $this->block->setProduct($this->product);
        $items = $this->block->getItems()->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected related products do not match actual related products!'
        );
    }

    /**
     * Test the position of related products in the block
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @return void
     */
    public function testPositionRelatedProducts(): void
    {
        $data = $this->getPositionData();
        $this->linkProducts('simple', $data['productLinks']);
        $this->product = $this->productRepository->get('simple');
        $this->block->setProduct($this->product);
        $items = $this->block->getItems()->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected related products do not match actual related products!'
        );
    }

    /**
     * Test the display of related products in the block on different websites
     *
     * @dataProvider multipleWebsitesLinkedProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoAppIsolation enabled
     * @param array $data
     * @return void
     */
    public function testMultipleWebsitesRelatedProducts(array $data): void
    {
        $this->updateProducts($this->prepareWebsiteIdsProducts());
        $productLinks = array_replace_recursive($this->existingProducts, $data['productLinks']);
        $this->linkProducts('simple-1', $productLinks);
        $this->product = $this->productRepository->get(
            'simple-1',
            false,
            $this->storeManager->getStore($data['storeCode'])->getId()
        );
        $this->block->setProduct($this->product);
        $items = $this->block->getItems()->getItems();

        $this->assertEquals(
            $data['expectedProductLinks'],
            $this->getActualLinks($items),
            'Expected related products do not match actual related products!'
        );
    }
}
