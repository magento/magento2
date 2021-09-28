<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleBundle\Block\Index as TestModuleBundleBlockIndex;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Magento\Bundle\Model\OptionList
 */
class LinksListTest extends TestCase
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Assert that added extension attributes wont affect getItems method
     *
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
     */
    public function testGetBundleById(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->product = $productRepository->get('bundle-product');

        $indexBlock = $this->objectManager->create(TestModuleBundleBlockIndex::class);
        $bundleById = $indexBlock->getBundleById($this->product->getEntityId());

        $this->assertEquals($this->product, $bundleById);
    }
}
