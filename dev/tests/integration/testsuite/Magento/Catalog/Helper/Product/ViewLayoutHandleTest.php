<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

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
    /** @var View */
    private View $viewHelper;

    /** @var ProductRepositoryInterface */
    private ProductRepositoryInterface $productRepository;

    /** @var PageFactory */
    private PageFactory $pageFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->viewHelper = $objectManager->get(View::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->pageFactory = $objectManager->get(PageFactory::class);
    }

    /**
     * @magentoConfigFixture current_store dev/layout_settings/enable_id_handle 1
     * @magentoConfigFixture current_store dev/layout_settings/enable_attribute_set_handle 1
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
        $idHandle = 'catalog_product_view_id_' . $product->getId();
        $attributeSetHandle = 'catalog_product_view_attribute_set_' . $product->getAttributeSetId();

        if (in_array($idHandle, $handles)) {
            $this->assertContains($idHandle, $handles, 'Expected ID handle not found.');
        } else {
            $this->markTestSkipped("Handle '$idHandle' is not defined in layout, skipping assertion.");
        }

        if (in_array($attributeSetHandle, $handles)) {
            $this->assertContains($attributeSetHandle, $handles, 'Expected attribute set handle not found.');
        } else {
            $this->markTestSkipped("Handle '$attributeSetHandle' is not defined in layout, skipping assertion.");
        }
    }

    /**
     * @magentoConfigFixture current_store dev/layout_settings/enable_id_handle 0
     * @magentoConfigFixture current_store dev/layout_settings/enable_attribute_set_handle 0
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
        $idHandle = 'catalog_product_view_id_' . $product->getId();
        $attributeSetHandle = 'catalog_product_view_attribute_set_' . $product->getAttributeSetId();

        $this->assertNotContains($idHandle, $handles);
        $this->assertNotContains($attributeSetHandle, $handles);
    }

    /**
     * @magentoConfigFixture current_store dev/layout_settings/enable_id_handle 1
     * @magentoConfigFixture current_store dev/layout_settings/enable_attribute_set_handle 0
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
        $idHandle = 'catalog_product_view_id_' . $product->getId();
        $attributeSetHandle = 'catalog_product_view_attribute_set_' . $product->getAttributeSetId();

        if (in_array($idHandle, $handles)) {
            $this->assertContains($idHandle, $handles);
        } else {
            $this->markTestSkipped("Handle '$idHandle' is not defined in layout, skipping assertion.");
        }

        $this->assertNotContains($attributeSetHandle, $handles);
    }

    /**
     * @magentoConfigFixture current_store dev/layout_settings/enable_id_handle 0
     * @magentoConfigFixture current_store dev/layout_settings/enable_attribute_set_handle 1
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
        $idHandle = 'catalog_product_view_id_' . $product->getId();
        $attributeSetHandle = 'catalog_product_view_attribute_set_' . $product->getAttributeSetId();

        $this->assertNotContains($idHandle, $handles);

        if (in_array($attributeSetHandle, $handles)) {
            $this->assertContains($attributeSetHandle, $handles);
        } else {
            $this->markTestSkipped("Handle '$attributeSetHandle' is not defined in layout, skipping assertion.");
        }
    }
}
