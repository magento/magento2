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
namespace Magento\Weee\Block\Item\Price;

use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Item price render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Tax\Block\Item\Price\Renderer
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Weee\Helper\Data $weeeHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->weeeHelper = $weeeHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $taxHelper, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Whether to display weee details together with price
     *
     * @return bool
     */
    public function displayPriceWithWeeeDetails()
    {
        if (!$this->weeeHelper->isEnabled()) {
            return false;
        }

        if (!$this->weeeHelper->typeOfDisplay([WeeeDisplayConfig::DISPLAY_INCL_DESCR], 'sales')) {
            return false;
        }

        if (!$this->getItem()->getWeeeTaxAppliedAmount()) {
            return false;
        }

        return true;
    }

    /**
     * Get display price for unit price including tax. The Weee amount will be added to unit price including tax
     * depending on Weee display setting
     *
     * @return float
     */
    public function getUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled()) {
            return $priceInclTax;
        }

        if ($this->weeeHelper
            ->typeOfDisplay([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')) {
            return $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
        }

        return $priceInclTax;
    }

    /**
     * Get display price for row total including tax. The Weee amount will be added to row total including tax
     * depending on Weee display setting
     *
     * @return float
     */
    public function getRowDisplayPriceInclTax()
    {
        $rowTotalInclTax = $this->getItem()->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled()) {
            return $rowTotalInclTax;
        }

        if ($this->weeeHelper->
            typeOfDisplay([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')) {
            return $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem());
        }

        return $rowTotalInclTax;
    }

    /**
     * Get display price for unit price excluding tax. The Weee amount will be added to unit price
     * depending on Weee display setting
     *
     * @return float
     */
    public function getUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItem()->getCalculationPrice();

        if (!$this->weeeHelper->isEnabled()) {
            return $priceExclTax;
        }

        if ($this->weeeHelper
            ->typeOfDisplay([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')) {
            return $priceExclTax + $this->getItem()->getWeeeTaxAppliedAmount();
        }

        return $priceExclTax;
    }

    /**
     * Get display price for row total excluding tax. The Weee amount will be added to row total
     * depending on Weee display setting
     *
     * @return float
     */
    public function getRowDisplayPriceExclTax()
    {
        $rowTotalExclTax = $this->getItem()->getRowTotal();

        if (!$this->weeeHelper->isEnabled()) {
            return $rowTotalExclTax;
        }

        if ($this->weeeHelper
            ->typeOfDisplay([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')) {
            return $rowTotalExclTax + $this->getItem()->getWeeeTaxAppliedRowAmount();
        }

        return $rowTotalExclTax;
    }

    /**
     * Get final unit display price including tax, this will add Weee amount to unit price include tax
     *
     * @return float
     */
    public function getFinalUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled()) {
            return $priceInclTax;
        }

        return $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final row display price including tax, this will add weee amount to rowTotalInclTax
     *
     * @return float
     */
    public function getFinalRowDisplayPriceInclTax()
    {
        $rowTotalInclTax = $this->getItem()->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled()) {
            return $rowTotalInclTax;
        }

        return $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final unit display price excluding tax
     *
     * @return float
     */
    public function getFinalUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItem()->getCalculationPrice();

        if (!$this->weeeHelper->isEnabled()) {
            return $priceExclTax;
        }

        return $priceExclTax + $this->getItem()->getWeeeTaxAppliedAmount();
    }

    /**
     * Get final row display price excluding tax, this will add Weee amount to rowTotal
     *
     * @return float
     */
    public function getFinalRowDisplayPriceExclTax()
    {
        $rowTotalExclTax = $this->getItem()->getRowTotal();

        if (!$this->weeeHelper->isEnabled()) {
            return $rowTotalExclTax;
        }

        return $rowTotalExclTax + $this->getItem()->getWeeeTaxAppliedRowAmount();
    }

    /**
     * Whether to display final price that include Weee amounts
     *
     * @return bool
     */
    public function displayFinalPrice()
    {
        if (!$this->weeeHelper->typeOfDisplay(WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL, 'sales')) {
            return false;
        }

        if (!$this->getItem()->getWeeeTaxAppliedAmount()) {
            return false;
        }
        return true;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }
}
