<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * The old price of a variation has to stay excluding tax when the option is selected on the storefront
 *
 * price-box.js replaces the content of the element by the amount stored under its data-price-type.
 */
class OldPriceExcludingTaxTest extends TestCase
{
    private const CHILD_SKU = 'simple_10';
    private const TAX_INCLUSIVE_PRICE = 10.0;
    private const TAX_EXCLUSIVE_PRICE = 9.3;

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/display/type 1
     * @magentoConfigFixture current_store tax/defaults/country US
     * @magentoConfigFixture current_store tax/defaults/region 1
     * @magentoConfigFixture current_store shipping/origin/country_id US
     * @magentoConfigFixture current_store shipping/origin/region_id 1
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testOldPriceTypePointsToTheAmountExcludingTax(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);

        $child = $productRepository->get(self::CHILD_SKU, true);
        $child->setTaxClassId(2);
        $child->setPrice(self::TAX_INCLUSIVE_PRICE);
        $child->setSpecialPrice(5.99);
        $productRepository->save($child);
        $product = $productRepository->get('configurable', false, null, true);

        $page = $objectManager->get(PageFactory::class)->create();
        $page->addHandle(['default', 'catalog_product_view']);
        $page->getLayout()->generateXml();
        $html = $page->getLayout()->getBlock('product.price.render.default')->render(
            FinalPrice::PRICE_CODE,
            $product,
            ['zone' => 'item_view']
        );

        $document = new \DOMDocument();
        $document->loadHTML($html, LIBXML_NOERROR);
        $oldPrice = (new \DOMXPath($document))
            ->query('//*[contains(@class,"old-price")]//*[@data-price-type]')
            ->item(0);
        $this->assertNotNull($oldPrice, 'The old price is not rendered: ' . $html);

        $block = $objectManager->create(Configurable::class);
        $block->setProduct($product);
        $config = $objectManager->get(SerializerInterface::class)->unserialize($block->getJsonConfig());
        $variationPrices = $config['optionPrices'][$child->getId()];

        $this->assertEqualsWithDelta(
            self::TAX_EXCLUSIVE_PRICE,
            (float)$oldPrice->getAttribute('data-price-amount'),
            0.01
        );
        $this->assertEqualsWithDelta(
            self::TAX_EXCLUSIVE_PRICE,
            $variationPrices[$oldPrice->getAttribute('data-price-type')]['amount'],
            0.01,
            'Selecting the option writes this amount into the old price'
        );
    }
}
