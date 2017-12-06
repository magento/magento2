<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * Tests product repository update should use provided store code.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductUpdate()
    {
        $sku = 'simple';
        $nameUpdated = 'updated';
        $product = $this->productRepository->get($sku, false, 0);
        $product->setName($nameUpdated);
        $this->productRepository->save($product);
        $product = $this->productRepository->get($sku, false, 0);
        self::assertEquals(
            $nameUpdated,
            $product->getName()
        );
    }
}
