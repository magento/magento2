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

namespace Magento\Tax\Pricing\Render;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @method string getIdSuffix()
 * @method string getDisplayLabel()
 */
class Adjustment extends AbstractAdjustment
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param Template\Context $context
     * @param \Magento\Tax\Helper\Data $helper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $helper,
        array $data = []
    ) {
        $this->taxHelper = $helper;
        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * @return string
     */
    protected function apply()
    {
        if ($this->displayBothPrices()) {
            if ($this->getZone() !== \Magento\Framework\Pricing\Render::ZONE_ITEM_OPTION) {
                $this->amountRender->setPriceDisplayLabel(__('Incl. Tax'));
            }
            $this->amountRender->setPriceWrapperCss('price-including-tax');
            $this->amountRender->setPriceId(
                $this->buildIdWithPrefix('price-including-tax-')
            );
        } elseif ($this->displayPriceExcludingTax()) {
            $this->amountRender->setDisplayValue(
                $this->amountRender->getAmount()->getValue($this->getAdjustmentCode())
            );
        }
        return $this->toHtml();
    }

    /**
     * Obtain code of adjustment type
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        //@TODO We can build two model using DI, not code. What about passing it in constructor?
        return \Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * Define if both prices should be displayed
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        return $this->taxHelper->displayBothPrices();
    }

    /**
     * Obtain display amount excluding tax
     *
     * @param bool $includeContainer
     * @return string
     */
    public function getDisplayAmountExclTax($includeContainer = false)
    {
        // todo use 'excludeWith' method instead hard-coded list here
        return $this->convertAndFormatCurrency(
            $this->amountRender->getAmount()->getValue(['tax', 'weee']),
            $includeContainer
        );
    }

    /**
     * Obtain display amount
     *
     * @param bool $includeContainer
     * @return string
     */
    public function getDisplayAmount($includeContainer = true)
    {
         return $this->convertAndFormatCurrency($this->amountRender->getAmount()->getValue(), $includeContainer);
    }

    /**
     * Build identifier with prefix
     *
     * @param string $prefix
     * @return string
     */
    public function buildIdWithPrefix($prefix)
    {
        $priceId = $this->getPriceId();
        if (!$priceId) {
            $priceId = $this->getSaleableItem()->getId();
        }
        return $prefix . $priceId . $this->getIdSuffix();
    }

    /**
     * Should be displayed price including tax
     *
     * @return bool
     */
    public function displayPriceIncludingTax()
    {
        return $this->taxHelper->displayPriceIncludingTax();
    }

    /**
     * Should be displayed price excluding tax
     *
     * @return bool
     */
    public function displayPriceExcludingTax()
    {
        return $this->taxHelper->displayPriceExcludingTax();
    }
}
