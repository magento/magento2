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

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adjustment render abstract
 *
 * @method string getZone()
 */
abstract class AbstractAdjustment extends Template implements AdjustmentRenderInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AmountRenderInterface
     */
    protected $amountRender;

    /**
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @param AmountRenderInterface $amountRender
     * @param array $arguments
     * @return string
     */
    public function render(AmountRenderInterface $amountRender, array $arguments = [])
    {
        $this->amountRender = $amountRender;

        $origArguments = $this->getData();
        $this->setData(array_replace($origArguments, $arguments));

        $html = $this->apply();

        // restore original block arguments
        $this->setData($origArguments);
        return $html;
    }

    /**
     * @return AmountRenderInterface
     */
    public function getAmountRender()
    {
        return $this->amountRender;
    }

    /**
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPriceType($priceCode)
    {
        return $this->getSaleableItem()->getPriceInfo()->getPrice($priceCode);
    }

    /**
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function getPrice()
    {
        return $this->amountRender->getPrice();
    }

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem()
    {
        return $this->amountRender->getSaleableItem();
    }

    /**
     * Convert and format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return string
     */
    public function convertAndFormatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer, $precision);
    }

    /**
     * @return \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
     */
    public function getAdjustment()
    {
        return $this->getSaleableItem()->getPriceInfo()->getAdjustment($this->getAdjustmentCode());
    }

    /**
     * @return string
     */
    abstract protected function apply();
}
