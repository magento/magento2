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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Catalog\Product;

/**
 * Bundle product price block
 *
 */
class Price extends \Magento\Catalog\Block\Product\Price
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalc;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Model\Calculation $taxCalc
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Math\Random $mathRandom,
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
        $request = $this->_taxCalc->getRateRequest(false, false, false);
        $request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = $this->_taxCalc->getRate($request);

        $request = $this->_taxCalc->getRateRequest();
        $request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = $this->_taxCalc->getRate($request);

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
        if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
            && $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false
        ) {
            return false;
        }
        return $this->_taxData->displayBothPrices();
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return bool|\Magento\Store\Model\Website
     */
    public function getWebsite($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsite();
    }
}
