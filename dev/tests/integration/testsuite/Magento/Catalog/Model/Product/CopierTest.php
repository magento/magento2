<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\ProductRepository;

class CopierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    private $copier;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * Tests multiple duplication of the same product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testDoubleCopy()
    {
        $product = $this->productRepository->get('simple');

        $product1 = $this->copier->copy($product);
        $this->assertEquals(
            'simple-1',
            $product1->getSku()
        );
        $this->assertEquals(
            'simple-product-1',
            $product1->getUrlKey()
        );

        $product2 = $this->copier->copy($product);
        $this->assertEquals(
            'simple-2',
            $product2->getSku()
        );
        $this->assertEquals(
            'simple-product-2',
            $product2->getUrlKey()
        );
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->copier = $this->objectManager->get(Copier::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $skus = [
            'simple-1',
            'simple-2'
        ];
        foreach ($skus as $sku) {
            try {
                $product = $this->productRepository->get($sku, false, null, true);
                $this->productRepository->delete($product);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        parent::tearDown();
    }
}
