<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for Initialization Helper
 */
class HelperTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helper = Bootstrap::getObjectManager()->get(Helper::class);
    }

    /**
     * Test that method resets product data
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testInitializeFromData()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple1');

        $productData = [
            'weight' => null,
            'special_price' => null,
            'cost' => null,
            'description' => null,
            'short_description' => null,
            'meta_description' => null,
            'meta_keyword' => null,
            'meta_title' => null,
        ];

        $resultProduct = $this->helper->initializeFromData($product, $productData);

        foreach (array_keys($productData) as $key) {
            $this->assertEquals(null, $resultProduct->getData($key));
        }
    }
}
