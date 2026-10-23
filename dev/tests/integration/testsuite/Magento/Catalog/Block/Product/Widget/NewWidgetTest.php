<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\Widget;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Widget\NewWidget as NewWidgetBlock;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Catalog New Products widget block and templates.
 *
 * @magentoDbIsolation disabled
 */
class NewWidgetTest extends TestCase
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $groupManagement = $objectManager->get(\Magento\Customer\Api\GroupManagementInterface::class);
        $notLoggedInId = $groupManagement->getNotLoggedInGroup()->getId();
        $objectManager->get(HttpContext::class)->setValue(
            CustomerContext::CONTEXT_GROUP,
            $notLoggedInId,
            $notLoggedInId
        );
        $this->layout = $objectManager->get(LayoutInterface::class);
    }

    /**
     * @return NewWidgetBlock
     */
    private function createBlock(string $template): NewWidgetBlock
    {
        /** @var NewWidgetBlock $block */
        $block = $this->layout->createBlock(NewWidgetBlock::class);
        $block->setTemplate($template);
        $block->setLayout($this->layout);
        $block->setData('display_type', NewWidgetBlock::DISPLAY_TYPE_NEW_PRODUCTS);
        $block->setData('products_count', 10);
        return $block;
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testGetAddToCartPostParams(): void
    {
        $product = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->get('simple');
        $block = $this->createBlock('product/widget/new/content/new_grid.phtml');
        $params = $block->getAddToCartPostParams($product);
        $this->assertArrayHasKey('action', $params);
        $this->assertArrayHasKey('data', $params);
        $this->assertSame((int) $product->getEntityId(), $params['data']['product']);
        $this->assertNotEmpty($params['data']['uenc']);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testGridTemplateRendersAddToCartFormWithCatalogWidget(): void
    {
        $block = $this->createBlock('product/widget/new/content/new_grid.phtml');
        $html = $block->toHtml();
        $this->assertStringContainsString('block-new-products', $html);
        $this->assertStringContainsString('data-role="tocart-form"', $html);
        $this->assertStringContainsString('data-mage-init=\'{"catalogAddToCart": {}}\'', $html);
        $this->assertStringContainsString('data-product-sku=', $html);
        $this->assertStringNotContainsString('product_sku', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<button[^>]+data-post=[^>]+class="[^"]*tocart/',
            $html
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testListTemplateRendersAddToCartFormWithCatalogWidget(): void
    {
        $block = $this->createBlock('product/widget/new/content/new_list.phtml');
        $html = $block->toHtml();
        $this->assertStringContainsString('block-new-products list', $html);
        $this->assertStringContainsString('data-role="tocart-form"', $html);
        $this->assertStringContainsString('data-mage-init=\'{"catalogAddToCart": {}}\'', $html);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGridTemplateUsesRedirectForConfigurableProduct(): void
    {
        $block = $this->createBlock('product/widget/new/content/new_grid.phtml');
        $block->setData('display_type', NewWidgetBlock::DISPLAY_TYPE_ALL_PRODUCTS);
        $html = $block->toHtml();
        $this->assertStringContainsString('redirectUrl', $html);
    }

    /**
     * Empty new-products collection should not render widget markup.
     */
    public function testEmptyCollectionRendersNothing(): void
    {
        $block = $this->createBlock('product/widget/new/content/new_grid.phtml');
        $block->setData('display_type', NewWidgetBlock::DISPLAY_TYPE_NEW_PRODUCTS);
        $this->assertSame('', trim($block->toHtml()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testTwoWidgetInstancesEachRenderIndependentForms(): void
    {
        $gridBlock = $this->createBlock('product/widget/new/content/new_grid.phtml');
        $listBlock = $this->createBlock('product/widget/new/content/new_list.phtml');
        $gridHtml = $gridBlock->toHtml();
        $listHtml = $listBlock->toHtml();
        $this->assertSame(1, substr_count($gridHtml, 'data-role="tocart-form"'));
        $this->assertSame(1, substr_count($listHtml, 'data-role="tocart-form"'));
    }
}
