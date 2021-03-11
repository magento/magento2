<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of basic logic for bundle options view
 */
abstract class AbstractBundleOptionsViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Registry */
    private $registry;

    /** @var PageFactory */
    private $pageFactory;

    /** @var ProductResource */
    private $productResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var string */
    private $selectLabelXpath = "//fieldset[contains(@class, 'fieldset-bundle-options')]"
    . "//label/span[normalize-space(text()) = '%s']";

    /** @var string */
    private $backToProductDetailButtonXpath = "//button[contains(@class, 'back customization')]";

    /** @var string */
    private $titleXpath = "//fieldset[contains(@class, 'bundle-options')]//span[contains(text(), 'Customize %s')]";

    /** @var string */
    private $singleOptionXpath = "//input[contains(@class, 'bundle-option') and contains(@type, 'hidden')]";

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');

        parent::tearDown();
    }

    /**
     * Process bundle options view with few selections
     *
     * @param string $sku
     * @param string $optionsSelectLabel
     * @param array $expectedSelectionsNames
     * @param bool $requiredOption
     * @return void
     */
    public function processMultiSelectionsView(
        string $sku,
        string $optionsSelectLabel,
        array $expectedSelectionsNames,
        bool $requiredOption = false
    ): void {
        $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
        $result = $this->renderProductOptionsBlock($product);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->backToProductDetailButtonXpath, $result),
            "'Back to product details' button doesn't exist on the page"
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->selectLabelXpath, $optionsSelectLabel), $result),
            'Options select label does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->titleXpath, $product->getName()), $result),
            sprintf('Customize %s label does not exist on the page', $product->getName())
        );
        $selectPath = $requiredOption ? $this->getRequiredSelectXpath() : $this->getNotRequiredSelectXpath();
        foreach ($expectedSelectionsNames as $selection) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($selectPath, $selection), $result),
                sprintf('Option for product named %s does not exist on the page', $selection)
            );
        }
    }

    /**
     * Process bundle options view with single selection
     *
     * @param string $sku
     * @param string $optionsSelectLabel
     * @return void
     */
    protected function processSingleSelectionView(string $sku, string $optionsSelectLabel): void
    {
        $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
        $result = $this->renderProductOptionsBlock($product);
        $this->assertEquals(1, Xpath::getElementsCountForXpath($this->backToProductDetailButtonXpath, $result));
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->selectLabelXpath, $optionsSelectLabel), $result),
            'Options select label does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->singleOptionXpath, $result),
            'Bundle product options select with single option does not display correctly'
        );
    }

    /**
     * Register product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');
        $this->registry->register('product', $product);
        $this->registry->register('current_product', $product);
    }

    /**
     * Render bundle product options block
     *
     * @param ProductInterface $product
     * @return string
     */
    private function renderProductOptionsBlock(ProductInterface $product): string
    {
        $this->registerProduct($product);
        $page = $this->pageFactory->create();
        $page->addHandle(['default', 'catalog_product_view', 'catalog_product_view_type_bundle']);
        $page->getLayout()->generateXml();
        $block = $page->getLayout()->getBlock('product.info.bundle.options');

        return $block->toHtml();
    }

    /**
     * Get required select Xpath
     *
     * @return string
     */
    abstract protected function getRequiredSelectXpath(): string;

    /**
     * Get not required select Xpath
     *
     * @return string
     */
    abstract protected function getNotRequiredSelectXpath(): string;
}
