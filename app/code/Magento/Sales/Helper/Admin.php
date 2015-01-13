<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Helper;

class Admin extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context);
    }

    /**
     * Display price attribute value in base order currency and in place order currency
     *
     * @param   \Magento\Framework\Object $dataObject
     * @param   string $code
     * @param   bool $strong
     * @param   string $separator
     * @return  string
     */
    public function displayPriceAttribute($dataObject, $code, $strong = false, $separator = '<br/>')
    {
        // Fix for 'bs_customer_bal_total_refunded' attribute
        $baseValue = $dataObject->hasData('bs_' . $code)
            ? $dataObject->getData('bs_' . $code)
            : $dataObject->getData('base_' . $code);
        return $this->displayPrices(
            $dataObject,
            $baseValue,
            $dataObject->getData($code),
            $strong,
            $separator
        );
    }

    /**
     * Get "double" prices html (block with base and place currency)
     *
     * @param   \Magento\Framework\Object $dataObject
     * @param   float $basePrice
     * @param   float $price
     * @param   bool $strong
     * @param   string $separator
     * @return  string
     */
    public function displayPrices($dataObject, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        $order = false;
        if ($dataObject instanceof \Magento\Sales\Model\Order) {
            $order = $dataObject;
        } else {
            $order = $dataObject->getOrder();
        }

        if ($order && $order->isCurrencyDifferent()) {
            $res = '<strong>';
            $res .= $order->formatBasePrice($basePrice);
            $res .= '</strong>' . $separator;
            $res .= '[' . $order->formatPrice($price) . ']';
        } elseif ($order) {
            $res = $order->formatPrice($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        } else {
            $res = $this->priceCurrency->format($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
    }

    /**
     * Filter collection by removing not available product types
     *
     * @param \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection $collection
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function applySalableProductTypesFilter($collection)
    {
        $productTypes = $this->_salesConfig->getAvailableProductTypes();
        foreach ($collection->getItems() as $key => $item) {
            if ($item instanceof \Magento\Catalog\Model\Product) {
                $type = $item->getTypeId();
            } elseif ($item instanceof \Magento\Sales\Model\Order\Item) {
                $type = $item->getProductType();
            } elseif ($item instanceof \Magento\Sales\Model\Quote\Item) {
                $type = $item->getProductType();
            } else {
                $type = '';
            }
            if (!in_array($type, $productTypes)) {
                $collection->removeItemByKey($key);
            }
        }
        return $collection;
    }
}
