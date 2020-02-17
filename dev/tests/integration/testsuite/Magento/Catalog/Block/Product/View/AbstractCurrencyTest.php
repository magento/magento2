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
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of general logic for currency tests
 */
abstract class AbstractCurrencyTest extends TestCase
{
    protected const TIER_PRICE_BLOCK_NAME = 'product.price.tier';
    protected const FINAL_PRICE_BLOCK_NAME = 'product.price.final';

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Registry */
    protected $registry;

    /** @var PageFactory */
    private $pageFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @inheridoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * Process price view on product page
     *
     * @param string|ProductInterface $product
     * @param string $blockName
     * @return string
     */
    protected function processPriceView($product, string $blockName = self::FINAL_PRICE_BLOCK_NAME): string
    {
        $product = is_string($product) ? $this->productRepository->get($product) : $product;
        $this->registerProduct($product);

        return trim(
            preg_replace('/(?:\s|&nbsp;)+/', ' ', strip_tags($this->getProductPriceBlockHtml($blockName)))
        );
    }

    /**
     * Get product price block content
     *
     * @param string $blockName
     * @return string
     */
    private function getProductPriceBlockHtml(string $blockName): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'catalog_product_view',
            'catalog_product_view_type_configurable',
        ]);
        $page->getLayout()->generateXml();
        $block = $page->getLayout()->getBlock($blockName);
        $this->assertNotFalse($block);

        return $block->toHtml();
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
