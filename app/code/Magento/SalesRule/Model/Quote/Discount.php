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
namespace Magento\SalesRule\Model\Quote;

use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Discount extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $_calculator;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_eventManager = $eventManager;
        $this->setCode('discount');
        $this->_calculator = $validator;
        $this->_storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect address discount amount
     *
     * @param Address $address
     * @return $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = $this->_storeManager->getStore($quote->getStoreId());
        $this->_calculator->reset($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $eventArgs = array(
            'website_id' => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode()
        );

        $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->_calculator->initTotals($items, $address);

        $address->setDiscountDescription(array());

        $items = $this->_calculator->sortItemsByPriority($items);
        /** @var \Magento\Sales\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getNoDiscount() || !$this->_calculator->canApplyDiscount($item)) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);

                // ensure my children are zeroed out
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $child->setDiscountAmount(0);
                        $child->setBaseDiscountAmount(0);
                    }
                }
                continue;
            }
            // to determine the child item discount, we calculate the parent
            if ($item->getParentItem()) {
                continue;
            }

            $eventArgs['item'] = $item;
            $this->_eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $this->_calculator->process($item);
                $this->distributeDiscount($item);
                foreach ($item->getChildren() as $child) {
                    $eventArgs['item'] = $child;
                    $this->_eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

                    $this->_aggregateItemDiscount($child);
                }
            } else {
                $this->_calculator->process($item);
                $this->_aggregateItemDiscount($item);
            }
        }

        /**
         * Process shipping amount discount
         */
        $address->setShippingDiscountAmount(0);
        $address->setBaseShippingDiscountAmount(0);
        if ($address->getShippingAmount()) {
            $this->_calculator->processShippingAmount($address);
            $this->_addAmount(-$address->getShippingDiscountAmount());
            $this->_addBaseAmount(-$address->getBaseShippingDiscountAmount());
        }

        $this->_calculator->prepareDescription($address);
        return $this;
    }

    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param AbstractItem $item
     * @return $this
     */
    protected function _aggregateItemDiscount($item)
    {
        $this->_addAmount(-$item->getDiscountAmount());
        $this->_addBaseAmount(-$item->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Distribute discount at parent item to children items
     *
     * @param AbstractItem $item
     * @return $this
     */
    protected function distributeDiscount(AbstractItem $item)
    {
        $parentBaseRowTotal = $item->getBaseRowTotal();
        $keys = [
            'discount_amount',
            'base_discount_amount',
            'original_discount_amount',
            'base_original_discount_amount',
        ];
        $roundingDelta = [];
        foreach ($keys as $key) {
            //Initialize the rounding delta to a tiny number to avoid floating point precision problem
            $roundingDelta[$key] = 0.0000001;
        }
        foreach ($item->getChildren() as $child) {
            $ratio = $child->getBaseRowTotal() / $parentBaseRowTotal;
            foreach ($keys as $key) {
                if (!$item->hasData($key)) {
                    continue;
                }
                $value = $item->getData($key) * $ratio;
                $roundedValue = $this->priceCurrency->round($value + $roundingDelta[$key]);
                $roundingDelta[$key] += $value - $roundedValue;
                $child->setData($key, $roundedValue);
            }
        }

        foreach ($keys as $key) {
            $item->setData($key, 0);
        }
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param Address $address
     * @return $this
     */
    public function fetch(Address $address)
    {
        $amount = $address->getDiscountAmount();

        if ($amount != 0) {
            $description = $address->getDiscountDescription();
            $title = __('Discount');
            if (strlen($description)) {
                $title = __('Discount (%1)', $description);
            }
            $address->addTotal(array('code' => $this->getCode(), 'title' => $title, 'value' => $amount));
        }
        return $this;
    }
}
