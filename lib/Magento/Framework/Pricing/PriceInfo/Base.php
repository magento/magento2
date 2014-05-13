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

namespace Magento\Framework\Pricing\PriceInfo;

use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Adjustment\Collection;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Price\Collection as PriceCollection;

/**
 * Class Base
 * Price info base model
 */
class Base implements PriceInfoInterface
{
    /**
     * @var PriceCollection
     */
    protected $priceCollection;

    /**
     * @var Collection
     */
    protected $adjustmentCollection;

    /**
     * @param PriceCollection $prices
     * @param Collection $adjustmentCollection
     */
    public function __construct(
        PriceCollection $prices,
        Collection $adjustmentCollection
    ) {
        $this->adjustmentCollection = $adjustmentCollection;
        $this->priceCollection = $prices;
    }

    /**
     * Returns array of prices
     *
     * @return PriceCollection
     */
    public function getPrices()
    {
        return $this->priceCollection;
    }

    /**
     * Returns price by code
     *
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPrice($priceCode)
    {
        return $this->priceCollection->get($priceCode);
    }

    /**
     * Get all registered adjustments
     *
     * @return AdjustmentInterface[]
     */
    public function getAdjustments()
    {
        return $this->adjustmentCollection->getItems();
    }

    /**
     * Get adjustment by code
     *
     * @param string $adjustmentCode
     * @throws \InvalidArgumentException
     * @return AdjustmentInterface
     */
    public function getAdjustment($adjustmentCode)
    {
        return $this->adjustmentCollection->getItemByCode($adjustmentCode);
    }
}
