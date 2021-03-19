<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for product swatch attribute helper.
 *
 * @see \Magento\Swatches\Helper\Data
 * @magentoDbIsolation enabled
 */
class DataTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Data */
    private $helper;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(Data::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testGetSwatchAttributesAsArray(): void
    {
        $product = $this->productRepository->get('simple2');
        $this->assertEmpty($this->helper->getSwatchAttributesAsArray($product));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_disabled_image.php
     *
     * @return void
     */
    public function testGetProductMediaGalleryWithHiddenImage(): void
    {
        $result = $this->helper->getProductMediaGallery($this->productRepository->get('simple_with_disabled_img'));
        $this->assertEmpty($result);
    }
}
