<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Catalog\Product;

/**
 * Bundle product price block
 *
 * @category   Magento
 * @package    Magento_Bundle
 */
class Price extends \Magento\Catalog\Block\Product\Price
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalc;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Registry $registry
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Model\Calculation $taxCalc
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Registry $registry,
        \Magento\Stdlib\String $string,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Model\Calculation $taxCalc,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $taxData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $data
        );
        $this->_taxCalc = $taxCalc;
    }

    /**
     * @return bool
     */
    public function isRatesGraterThenZero()
    {
        $_request = $this->_taxCalc->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = $this->_taxCalc->getRate($_request);

        $_request = $this->_taxCalc->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = $this->_taxCalc->getRate($_request);

        return floatval($defaultTax) > 0 || floatval($currentTax) > 0;
    }

    /**
     * Check if we have display prices including and excluding tax
     * With corrections for Dynamic prices
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        $product = $this->getProduct();
        if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC &&
            $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false
        ) {
            return false;
        }
        return $this->_taxData->displayBothPrices();
    }

    /**
     * Convert block to html string
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = $this->getProduct();
        if ($this->getMAPTemplate() && $this->_catalogData->canApplyMsrp(
            $product
        ) && $product->getPriceType() != \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ) {
            $hiddenPriceHtml = parent::_toHtml();
            if ($this->_catalogData->isShowPriceOnGesture($product)) {
                $this->setWithoutPrice(true);
            }
            $realPriceHtml = parent::_toHtml();
            $this->unsWithoutPrice();
            $addToCartUrl = $this->getLayout()->getBlock('product.info.bundle')->getAddToCartUrl($product);
            $product->setAddToCartUrl($addToCartUrl);
            $html = $this->getLayout()->createBlock(
                'Magento\Catalog\Block\Product\Price'
            )->setTemplate(
                $this->getMAPTemplate()
            )->setRealPriceHtml(
                $hiddenPriceHtml
            )->setPriceElementIdPrefix(
                'bundle-price-'
            )->setIdSuffix(
                $this->getIdSuffix()
            )->setProduct(
                $product
            )->toHtml();

            return $realPriceHtml . $html;
        }

        return parent::_toHtml();
    }

    /**
     * @param null|string|bool|int|\Magento\Core\Model\Store $storeId
     * @return bool|\Magento\Core\Model\Website
     */
    public function getWebsite($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsite();
    }
}
