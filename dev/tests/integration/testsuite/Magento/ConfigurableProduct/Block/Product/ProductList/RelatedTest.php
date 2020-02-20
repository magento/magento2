<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\ProductList;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ProductList\AbstractLinksTest;
use Magento\Catalog\Block\Product\ProductList\Related;

/**
 * Check the correct behavior of related products on the configurable product view page

 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class RelatedTest extends AbstractLinksTest
{
    /**
     * @var Related
     */
    protected $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Related::class);
        $this->linkType = 'related';
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @return void
     */
    public function testRenderConfigurableWithLinkedProduct(): void
    {
        $this->linkProducts('configurable', ['simple2' => ['position' => 1]]);
        $relatedProduct = $this->productRepository->get('simple2');
        $this->block->setProduct($this->productRepository->get('configurable'));
        $this->prepareBlock();
        $html = $this->block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains($relatedProduct->getName(), $html);
        $this->assertCount(1, $this->block->getItems());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @return void
     */
    public function testRenderConfigurableWithLinkedProductOnChild(): void
    {
        $this->linkProducts('simple_10', ['simple2' => ['position' => 1]]);
        $relatedProduct = $this->productRepository->get('simple2');
        $this->block->setProduct($this->productRepository->get('configurable'));
        $this->prepareBlock();
        $html = $this->block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertNotContains($relatedProduct->getName(), $html);
        $this->assertEmpty($this->block->getItems());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @return void
     */
    public function testLinkedProductsPosition(): void
    {
        $this->linkProducts(
            'configurable',
            ['wrong-simple' => ['position' => 3], 'simple-249' => ['position' => 2], 'simple-156' => ['position' => 1]]
        );
        $this->block->setProduct($this->productRepository->get('configurable'));
        $this->assertEquals(
            ['simple-156', 'simple-249','wrong-simple'],
            $this->getActualLinks($this->getLinkedItems()),
            'Expected linked products do not match actual linked products!'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @return void
     */
    public function testGetIdentities(): void
    {
        $this->linkProducts('configurable', ['simple2' => ['position = 1']]);
        $relatedProduct = $this->productRepository->get('simple2');
        $this->block->setProduct($this->productRepository->get('configurable'));
        $this->prepareBlock();
        $this->assertEquals(['cat_p_' . $relatedProduct->getId(), 'cat_p'], $this->block->getIdentities());
    }

    /**
     * Returns linked products from block.
     *
     * @return ProductInterface[]
     */
    protected function getLinkedItems(): array
    {
        return $this->block->getItems()->getItems();
    }
}
