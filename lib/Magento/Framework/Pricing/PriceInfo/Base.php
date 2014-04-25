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

use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\PriceComposite;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Adjustment\Collection;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Price info base model
 */
class Base implements PriceInfoInterface
{
    /**
     * @var SaleableInterface
     */
    protected $saleableItem;

    /**
     * @var PriceComposite
     */
    protected $prices;

    /**
     * @var PriceInterface[]
     */
    protected $priceInstances;

    /**
     * @var Collection
     */
    protected $adjustmentCollection;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @param SaleableInterface $saleableItem
     * @param PriceComposite $prices
     * @param Collection $adjustmentCollection
     * @param AmountFactory $amountFactory
     * @param float $quantity
     */
    public function __construct(
        SaleableInterface $saleableItem,
        PriceComposite $prices,
        Collection $adjustmentCollection,
        AmountFactory $amountFactory,
        $quantity = self::PRODUCT_QUANTITY_DEFAULT
    ) {
        $this->saleableItem = $saleableItem;
        $this->prices = $prices;
        $this->adjustmentCollection = $adjustmentCollection;
        $this->amountFactory = $amountFactory;
        $this->quantity = $quantity;
    }

    /**
     * @return PriceInterface[]
     */
    public function getPrices()
    {
        // check if all prices initialized
        $this->initPrices();
        return $this->priceInstances;
    }

    /**
     * Init price types
     *
     * @return $this
     */
    protected function initPrices()
    {
        $prices = $this->prices->getPriceCodes();
        foreach ($prices as $code) {
            if (!isset($this->priceInstances[$code])) {
                $this->priceInstances[$code] = $this->prices->createPriceObject(
                    $this->saleableItem,
                    $code,
                    $this->quantity
                );
            }
        }
        return $this;
    }

    /**
     * @param string $priceCode
     * @param float|null $quantity
     * @return PriceInterface
     */
    public function getPrice($priceCode, $quantity = null)
    {
        if (!isset($this->priceInstances[$priceCode]) && $quantity === null) {
            $this->priceInstances[$priceCode] = $this->prices->createPriceObject(
                $this->saleableItem,
                $priceCode,
                $this->quantity
            );
            return $this->priceInstances[$priceCode];
        } elseif (isset($this->priceInstances[$priceCode]) && $quantity === null) {
            return $this->priceInstances[$priceCode];
        } else {
            return $this->prices->createPriceObject($this->saleableItem, $priceCode, $quantity);
        }
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

    /**
     * Returns prices included in base price
     *
     * @return array
     */
    public function getPricesIncludedInBase()
    {
        $prices = [];
        foreach ($this->prices->getMetadata() as $code => $price) {
            if (isset($price['include_in_base_price']) && $price['include_in_base_price']) {
                $priceModel = $this->getPrice($code, $this->quantity);
                if ($priceModel->getValue() !== false) {
                    $prices[] = $priceModel;
                }
            }
        }
        return $prices;
    }
}
