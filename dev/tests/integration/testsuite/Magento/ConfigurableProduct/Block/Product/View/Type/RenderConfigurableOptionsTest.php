<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to render configurable options.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class RenderConfigurableOptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Data
     */
    private $configurableHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigurableAttributeData
     */
    private $configurableAttributeData;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configurableHelper = $this->objectManager->get(Data::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->configurableAttributeData = $this->objectManager->get(ConfigurableAttributeData::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->page = $this->objectManager->create(Page::class);
        $this->json = $this->objectManager->get(Json::class);
        parent::setUp();
    }

    /**
     * Assert that all configurable options was rendered correctly if one of
     * child product is visible on catalog\search.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     *
     * @return void
     */
    public function testRenderConfigurableOptionsBlockWithOneVisibleOption(): void
    {
        $configurableProduct = $this->productRepository->get('Configurable product');
        $childProduct = $this->productRepository->get('Simple option 1');
        $childProduct->setVisibility(Visibility::VISIBILITY_BOTH);
        $this->productRepository->save($childProduct);
        $allProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct, null);
        $options = $this->configurableHelper->getOptions($configurableProduct, $allProducts);
        $confAttrData = $this->configurableAttributeData->getAttributesData($configurableProduct, $options);
        $attributesJson = str_replace(
            ['[', ']'],
            ['\[', '\]'],
            $this->json->serialize($confAttrData['attributes'])
        );
        $optionsHtml = $this->getConfigurableOptionsHtml('Configurable product');
        $this->assertMatchesRegularExpression("/\"spConfig\": {\"attributes\":{$attributesJson}/", $optionsHtml);
    }

    /**
     * Render configurable options block.
     *
     * @param string $configurableSku
     * @return string
     */
    private function getConfigurableOptionsHtml(string $configurableSku): string
    {
        $product = $this->productRepository->get($configurableSku);
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
        $optionsBlock = $this->getOptionsWrapperBlockWithOnlyConfigurableBlock();
        $optionHtml = $optionsBlock->toHtml();
        $this->registry->unregister('product');

        return $optionHtml;
    }

    /**
     * Get options wrapper without extra blocks(only configurable child block).
     *
     * @return View
     */
    private function getOptionsWrapperBlockWithOnlyConfigurableBlock(): View
    {
        $this->page->addHandle([
            'default',
            'catalog_product_view',
            'catalog_product_view_type_configurable',
        ]);
        $this->page->getLayout()->generateXml();

        /** @var View $productInfoOptionsWrapper */
        $productInfoOptionsWrapper = $this->page->getLayout()->getBlock('product.info.options.wrapper');
        $productInfoOptionsWrapper->unsetChild('product_options');
        $productInfoOptionsWrapper->unsetChild('html_calendar');

        return $productInfoOptionsWrapper;
    }
}
