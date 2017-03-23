<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml block for fieldset of grouped product
 */
namespace Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * Redefine default price block
     * Set current customer to tax calculation
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_useLinkForAsLowAs = false;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        $product = $this->getData('product');
        if ($product->getTypeInstance()->getStoreFilter($product) === null) {
            $product->getTypeInstance()->setStoreFilter(
                $this->_storeManager->getStore($product->getStoreId()),
                $product
            );
        }

        return $product;
    }

    /**
     * Retrieve array of associated products
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        $product = $this->getProduct();
        $result = $product->getTypeInstance()->getAssociatedProducts($product);

        $storeId = $product->getStoreId();
        foreach ($result as $item) {
            $item->setStoreId($storeId);
        }

        return $result;
    }

    /**
     * Set preconfigured values to grouped associated products
     *
     * @return $this
     */
    public function setPreconfiguredValue()
    {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getAssociatedProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }

    /**
     * Check whether the price can be shown for the specified product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCanShowProductPrice($product)
    {
        return true;
    }

    /**
     * Checks whether block is last fieldset in popup
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsLastFieldset()
    {
        $isLast = $this->getData('is_last_fieldset');
        if (!$isLast) {
            $options = $this->getProduct()->getOptions();
            return !$options || !count($options);
        }
        return $isLast;
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->pricingHelper->currencyByStore($price, $store, false);
    }
}
