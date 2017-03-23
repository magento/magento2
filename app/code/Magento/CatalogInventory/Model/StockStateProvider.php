<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Framework\DataObject\Factory as ObjectFactory;

/**
 * Interface StockStateProvider
 */
class StockStateProvider implements StockStateProviderInterface
{
    /**
     * @var MathDivision
     */
    protected $mathDivision;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var bool
     */
    protected $qtyCheckApplicable;

    /**
     * @param MathDivision $mathDivision
     * @param FormatInterface $localeFormat
     * @param ObjectFactory $objectFactory
     * @param ProductFactory $productFactory
     * @param bool $qtyCheckApplicable
     */
    public function __construct(
        MathDivision $mathDivision,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        ProductFactory $productFactory,
        $qtyCheckApplicable = true
    ) {
        $this->mathDivision = $mathDivision;
        $this->localeFormat = $localeFormat;
        $this->objectFactory = $objectFactory;
        $this->productFactory = $productFactory;
        $this->qtyCheckApplicable = $qtyCheckApplicable;
    }

    /**
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function verifyStock(StockItemInterface $stockItem)
    {
        if ($stockItem->getQty() === null && $stockItem->getManageStock()) {
            return false;
        }
        if ($stockItem->getBackorders() == StockItemInterface::BACKORDERS_NO
            && $stockItem->getQty() <= $stockItem->getMinQty()
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function verifyNotification(StockItemInterface $stockItem)
    {
        return (float)$stockItem->getQty() < $stockItem->getNotifyStockQty();
    }

    /**
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function checkQuoteItemQty(StockItemInterface $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = $this->getNumber($qty);

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($stockItem->getIsQtyDecimal());
        if (!$stockItem->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);
            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);
            $qty = $this->getNumber($qty);
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The fewest you may purchase is %1.', $stockItem->getMinSaleQty() * 1))
                ->setErrorCode('qty_min')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The most you may purchase is %1.', $stockItem->getMaxSaleQty() * 1))
                ->setErrorCode('qty_max')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($stockItem, $qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$stockItem->getManageStock()) {
            return $result;
        }

        if (!$stockItem->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage(__('This product is out of stock.'))
                ->setQuoteMessage(__('Some of the products are out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($stockItem, $summaryQty) || !$this->checkQty($stockItem, $qty)) {
            $message = __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName());
            $result->setHasError(true)->setMessage($message)->setQuoteMessage($message)->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if ($stockItem->getQty() - $summaryQty < 0) {
                if ($stockItem->getProductName()) {
                    if ($stockItem->getIsChildItem()) {
                        $backOrderQty = $stockItem->getQty() > 0 ? ($summaryQty - $stockItem->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = (int)$stockItem->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($stockItem->getQty() - ($summaryQty - $qty)) * 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $stockItem->setOrderedItems($orderedItems + $qty);
                    }

                    if ($stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$stockItem->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, so we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($stockItem->getShowDefaultNotificationMessage()) {
                        $result->setMessage(
                            __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName())
                        );
                    }
                }
            } else {
                if (!$stockItem->getIsChildItem()) {
                    $stockItem->setOrderedItems($qty + (int)$stockItem->getOrderedItems());
                }
            }
        }
        return $result;
    }

    /**
     * Check quantity
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @exception \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function checkQty(StockItemInterface $stockItem, $qty)
    {
        if (!$this->qtyCheckApplicable) {
            return true;
        }
        if (!$stockItem->getManageStock()) {
            return true;
        }
        if ($stockItem->getQty() - $stockItem->getMinQty() - $qty < 0) {
            switch ($stockItem->getBackorders()) {
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY:
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
            }
        }
        return true;
    }

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @return int|float
     */
    public function suggestQty(StockItemInterface $stockItem, $qty)
    {
        // We do not manage stock
        if ($qty <= 0 || !$stockItem->getManageStock()) {
            return $qty;
        }

        $qtyIncrements = (int)$stockItem->getQtyIncrements();
        // Currently only integer increments supported
        if ($qtyIncrements < 2) {
            return $qty;
        }

        $minQty = max($stockItem->getMinSaleQty(), $qtyIncrements);
        $divisibleMin = ceil($minQty / $qtyIncrements) * $qtyIncrements;

        $maxQty = min($stockItem->getQty() - $stockItem->getMinQty(), $stockItem->getMaxSaleQty());
        $divisibleMax = floor($maxQty / $qtyIncrements) * $qtyIncrements;

        if ($qty < $minQty || $qty > $maxQty || $divisibleMin > $divisibleMax) {
            // Do not perform rounding for qty that does not satisfy min/max conditions to not confuse customer
            return $qty;
        }

        // Suggest value closest to given qty
        $closestDivisibleLeft = floor($qty / $qtyIncrements) * $qtyIncrements;
        $closestDivisibleRight = $closestDivisibleLeft + $qtyIncrements;
        $acceptableLeft = min(max($divisibleMin, $closestDivisibleLeft), $divisibleMax);
        $acceptableRight = max(min($divisibleMax, $closestDivisibleRight), $divisibleMin);
        return abs($acceptableLeft - $qty) < abs($acceptableRight - $qty) ? $acceptableLeft : $acceptableRight;
    }

    /**
     * @param StockItemInterface $stockItem
     * @param float|int $qty
     * @return \Magento\Framework\DataObject
     */
    public function checkQtyIncrements(StockItemInterface $stockItem, $qty)
    {
        $result = new \Magento\Framework\DataObject();
        if ($stockItem->getSuppressCheckQtyIncrements()) {
            return $result;
        }

        $qtyIncrements = $stockItem->getQtyIncrements() * 1;

        if ($qtyIncrements && $this->mathDivision->getExactDivision($qty, $qtyIncrements) != 0) {
            $result->setHasError(true)
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setErrorCode('qty_increments')
                ->setQuoteMessageIndex('qty');
            if ($stockItem->getIsChildItem()) {
                $result->setMessage(
                    __(
                        'You can buy %1 only in quantities of %2 at a time.',
                        $stockItem->getProductName(),
                        $qtyIncrements
                    )
                );
            } else {
                $result->setMessage(__('You can buy this product only in quantities of %1 at a time.', $qtyIncrements));
            }
        }
        return $result;
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param StockItemInterface $stockItem
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getStockQty(StockItemInterface $stockItem)
    {
        if (!$stockItem->hasStockQty()) {
            $stockItem->setStockQty(0);
            $product = $this->productFactory->create();
            $product->load($stockItem->getProductId());
            // prevent possible recursive loop
            if (!$product->isComposite()) {
                $stockQty = $stockItem->getQty();
            } else {
                $stockQty = null;
                $productsByGroups = $product->getTypeInstance()->getProductsToPurchaseByReqGroups($product);
                foreach ($productsByGroups as $productsInGroup) {
                    $qty = 0;
                    foreach ($productsInGroup as $childProduct) {
                        $qty += $this->getStockQty($stockItem);
                    }
                    if (null === $stockQty || $qty < $stockQty) {
                        $stockQty = $qty;
                    }
                }
            }
            $stockQty = (float)$stockQty;
            if ($stockQty < 0 || !$stockItem->getManageStock() || !$stockItem->getIsInStock()
                || !$product->isSaleable()
            ) {
                $stockQty = 0;
            }
            $stockItem->setStockQty($stockQty);
        }
        return (float)$stockItem->getData('stock_qty');
    }

    /**
     * @param string|float|int|null $qty
     * @return float|null
     */
    protected function getNumber($qty)
    {
        if (!is_numeric($qty)) {
            $qty = $this->localeFormat->getNumber($qty);
            return $qty;
        }
        return $qty;
    }
}
