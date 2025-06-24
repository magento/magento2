<?php

declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product\View;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for verifying layout handles based on system configuration.
 */
class ViewLayoutHandleTest extends TestCase
{
    private View $viewHelper;
    private ProductRepositoryInterface $productRepository;
    private PageFactory $pageFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->viewHelper = $objectManager->get(View::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->pageFactory = $objectManager->get(PageFactory::class);
    }

    /**
     * @magentoConfigFixture current_store catalog/layout_settings/enable_id_handle 1
     * @magentoConfigFixture current_store catalog/layout_settings/enable_attribute_set_handle 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testHandlesAreAddedWhenEnabled(): void
    {
        $product = $this->productRepository->get('simple');
        $page = $this->pageFactory->create();
        $this->viewHelper->initProductLayout($page, $product);

        $handles = $page->getLayout()->getUpdate()->getHandles();

        $this->assertContains('catalog_product_view_id_' . $product->getId(), $handles);
        $this->assertContains('catalog_product_view_attribute_set_' . $product->getAttributeSetId(), $handles);
    }

    /**
     * @magentoConfigFixture current_store catalog/layout_settings/enable_id_handle 0
     * @magentoConfigFixture current_store catalog/layout_settings/enable_attribute_set_handle 0
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testHandlesAreSkippedWhenDisabled(): void
    {
        $product = $this->productRepository->get('simple');
        $page = $this->pageFactory->create();
        $this->viewHelper->initProductLayout($page, $product);

        $handles = $page->getLayout()->getUpdate()->getHandles();

        $this->assertNotContains('catalog_product_view_id_' . $product->getId(), $handles);
        $this->assertNotContains('catalog_product_view_attribute_set_' . $product->getAttributeSetId(), $handles);
    }

    /**
     * @magentoConfigFixture current_store catalog/layout_settings/enable_id_handle 1
     * @magentoConfigFixture current_store catalog/layout_settings/enable_attribute_set_handle 0
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testHandleAddedOnlyForIdWhenAttributeSetDisabled(): void
    {
        $product = $this->productRepository->get('simple');
        $page = $this->pageFactory->create();
        $this->viewHelper->initProductLayout($page, $product);

        $handles = $page->getLayout()->getUpdate()->getHandles();

        $this->assertContains('catalog_product_view_id_' . $product->getId(), $handles);
        $this->assertNotContains('catalog_product_view_attribute_set_' . $product->getAttributeSetId(), $handles);
    }

    /**
     * @magentoConfigFixture current_store catalog/layout_settings/enable_id_handle 0
     * @magentoConfigFixture current_store catalog/layout_settings/enable_attribute_set_handle 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testHandleAddedOnlyForAttributeSetWhenIdDisabled(): void
    {
        $product = $this->productRepository->get('simple');
        $page = $this->pageFactory->create();
        $this->viewHelper->initProductLayout($page, $product);

        $handles = $page->getLayout()->getUpdate()->getHandles();

        $this->assertNotContains('catalog_product_view_id_' . $product->getId(), $handles);
        $this->assertContains('catalog_product_view_attribute_set_' . $product->getAttributeSetId(), $handles);
    }
}
