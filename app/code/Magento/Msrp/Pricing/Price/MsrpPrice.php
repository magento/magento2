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

namespace Magento\Msrp\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * MSRP price model
 */
class MsrpPrice extends FinalPrice implements MsrpPriceInterface
{
    /**
     * Price type MSRP
     */
    const PRICE_CODE = 'msrp_price';

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData;

    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $config;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Msrp\Helper\Data $msrpData
     * @param \Magento\Msrp\Model\Config $config
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Msrp\Helper\Data $msrpData,
        \Magento\Msrp\Model\Config $config
    ) {
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->msrpData = $msrpData;
        $this->config = $config;
    }

    /**
     * Returns whether the MSRP should be shown on gesture
     *
     * @return bool
     */
    public function isShowPriceOnGesture()
    {
        return $this->msrpData->isShowPriceOnGesture($this->product);
    }

    /**
     * Get Msrp message for price
     *
     * @return string
     */
    public function getMsrpPriceMessage()
    {
        return $this->msrpData->getMsrpPriceMessage($this->product);
    }

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     */
    public function isMsrpEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * Check if can apply Minimum Advertise price to product
     *
     * @param Product $product
     * @return bool
     */
    public function canApplyMsrp(Product $product)
    {
        return $this->msrpData->canApplyMsrp($product);
    }

    /**
     * @param Product $product
     * @return bool|float
     */
    public function isMinimalPriceLessMsrp(Product $product)
    {
        return $this->msrpData->isMinimalPriceLessMsrp($product);
    }
}
