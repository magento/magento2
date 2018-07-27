<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class for final_price rendering.
 *
 * @method bool getUseLinkForAsLowAs()
 * @method bool getDisplayMinimalPrice()
 */
class FinalPriceBox extends BasePriceBox
{
    /**
     * Interface resolver provided to check is product available for sale.
     *
     * @var \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface
     */
    private $salableResolver;

    /**
     * Module statuses manager.
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * Shows minimal value of Tier Prices.
     *
     * @var \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface
     */
    private $minimalPriceCalculator;

    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param array $data
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = [],
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver = null
    ) {
        parent::__construct($context, $saleableItem, $price, $rendererPool, $data);
        $this->salableResolver = $salableResolver ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface::class);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        // Check catalog permissions
        if ($this->getSaleableItem()->getCanShowPrice() === false) {
            return '';
        }

        $result = parent::_toHtml();

        //Renders MSRP in case it is enabled
        if ($this->isMsrpPriceApplicable()) {
            /** @var BasePriceBox $msrpBlock */
            $msrpBlock = $this->rendererPool->createPriceRender(
                MsrpPrice::PRICE_CODE,
                $this->getSaleableItem(),
                [
                    'real_price_html' => $result,
                    'zone' => $this->getZone(),
                ]
            );
            $result = $msrpBlock->toHtml();
        }

        return $this->wrapResult($result);
    }

    /**
     * Check is MSRP applicable for the current product.
     *
     * @return bool
     */
    protected function isMsrpPriceApplicable()
    {
        $moduleManager = $this->getModuleManager();

        if (!$moduleManager->isEnabled('Magento_Msrp') || !$moduleManager->isOutputEnabled('Magento_Msrp')) {
            return false;
        }

        try {
            /** @var MsrpPrice $msrpPriceType */
            $msrpPriceType = $this->getSaleableItem()->getPriceInfo()->getPrice('msrp_price');
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            return false;
        }

        if ($msrpPriceType === null) {
            return false;
        }

        $product = $this->getSaleableItem();

        return $msrpPriceType->canApplyMsrp($product) && $msrpPriceType->isMinimalPriceLessMsrp($product);
    }

    /**
     * Wrap with standard required container.
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';
    }

    /**
     * Render minimal amount.
     *
     * @return string
     */
    public function renderAmountMinimal()
    {
        $id = $this->getPriceId() ? $this->getPriceId() : 'product-minimal-price-' . $this->getSaleableItem()->getId();
        $amount = $this->getMinimalPriceCalculator()->getAmount($this->getSaleableItem());

        if ($amount === null) {
            return '';
        }

        return $this->renderAmount(
            $amount,
            [
                'display_label'     => __('As low as'),
                'price_id'          => $id,
                'include_container' => false,
                'skip_adjustments' => true
            ]
        );
    }

    /**
     * Define if the special price should be shown.
     *
     * @return bool
     */
    public function hasSpecialPrice()
    {
        $displayRegularPrice = $this->getPriceType(Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        $displayFinalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        return $displayFinalPrice < $displayRegularPrice;
    }

    /**
     * Define if the minimal price should be shown.
     *
     * @return bool
     */
    public function showMinimalPrice()
    {
        $minTierPrice = $this->getMinimalPriceCalculator()->getValue($this->getSaleableItem());

        /** @var Price\FinalPrice $finalPrice */
        $finalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE);
        $finalPriceValue = $finalPrice->getAmount()->getValue();

        return $this->getDisplayMinimalPrice()
        && $minTierPrice !== null
        && $minTierPrice < $finalPriceValue;
    }

    /**
     * Get Key for caching block content.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('list_category_page') ? '-list-category-page': '');
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeys = parent::getCacheKeyInfo();
        $cacheKeys['display_minimal_price'] = $this->getDisplayMinimalPrice();
        $cacheKeys['is_product_list'] = $this->isProductList();
        return $cacheKeys;
    }

    /**
     * @deprecated
     * @return \Magento\Framework\Module\Manager
     */
    private function getModuleManager()
    {
        if ($this->moduleManager === null) {
            $this->moduleManager = ObjectManager::getInstance()->get(\Magento\Framework\Module\Manager::class);
        }
        return $this->moduleManager;
    }

    /**
     * Get flag that price rendering should be done for the list of products.
     * By default (if flag is not set) is false.
     *
     * @return bool
     */
    public function isProductList()
    {
        $isProductList = $this->getData('is_product_list');
        return $isProductList === true;
    }

    /**
     * @deprecated
     * @return \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface
     */
    private function getMinimalPriceCalculator()
    {
        if ($this->minimalPriceCalculator == null) {
            $this->minimalPriceCalculator = ObjectManager::getInstance()
                ->get(\Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface::class);
        }

        return $this->minimalPriceCalculator;
    }
}
