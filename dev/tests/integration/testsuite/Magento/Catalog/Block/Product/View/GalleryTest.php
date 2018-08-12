<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

class GalleryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }
    /**
     * Tests rendered gallery block.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoAppArea frontend
     */
    public function testHtml()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple');
        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        /** @var Gallery $block */
        $block = $layout->createBlock(Gallery::class);
        $block->setData('product', $product);
        $block->setTemplate("Magento_Catalog::product/view/gallery.phtml");

        $showCaption = $block->getVar('gallery/caption');

        self::assertContains('"showCaption": ' . $showCaption, $block->toHtml());
    }
}
