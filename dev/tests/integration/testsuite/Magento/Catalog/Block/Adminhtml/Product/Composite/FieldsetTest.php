<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Composite;

use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test Fieldset block in composite product configuration layout
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset
 * @magentoAppArea adminhtml
 */
class FieldsetTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Page */
    private $page;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var string */
    private $fieldsetXpath = "//fieldset[@id='product_composite_configure_fields_%s']";

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_options.php
     * @return void
     */
    public function testRenderHtml(): void
    {
        $product = $this->productRepository->get('simple');
        $this->registerProduct($product);
        $this->preparePage();
        $fieldsetBlock = $this->page->getLayout()->getBlock('product.composite.fieldset');
        $this->assertNotFalse($fieldsetBlock, 'Expected fieldset block is missing!');
        $html = $fieldsetBlock->toHtml();

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->fieldsetXpath, 'options'), $html),
            'Expected options block is missing!'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->fieldsetXpath, 'qty'), $html),
            'Expected qty block is missing!'
        );
    }

    /**
     * Prepare page layout
     *
     * @return void
     */
    private function preparePage(): void
    {
        $this->page->addHandle([
            'default',
            'CATALOG_PRODUCT_COMPOSITE_CONFIGURE',
            'catalog_product_view_type_simple',
        ]);
        $this->page->getLayout()->generateXml();
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->registry->register('current_product', $product);
        $this->registry->register('product', $product);
    }
}
