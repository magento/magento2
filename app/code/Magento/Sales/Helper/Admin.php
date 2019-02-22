<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Helper;

/**
 * Sales admin helper.
 */
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
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_salesConfig = $salesConfig;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Display price attribute value in base order currency and in place order currency
     *
     * @param   \Magento\Framework\DataObject $dataObject
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
     * @param   \Magento\Framework\DataObject $dataObject
     * @param   float $basePrice
     * @param   float $price
     * @param   bool $strong
     * @param   string $separator
     * @return  string
     */
    public function displayPrices($dataObject, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
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
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function applySalableProductTypesFilter($collection)
    {
        $productTypes = $this->_salesConfig->getAvailableProductTypes();
        foreach ($collection->getItems() as $key => $item) {
            if ($item instanceof \Magento\Catalog\Model\Product) {
                $type = $item->getTypeId();
            } elseif ($item instanceof \Magento\Sales\Model\Order\Item) {
                $type = $item->getProductType();
            } elseif ($item instanceof \Magento\Quote\Model\Quote\Item) {
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

    /**
     * Escape string preserving links
     *
     * @param string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtmlWithLinks($data, $allowedTags = null)
    {
        if (!empty($data) && is_array($allowedTags) && in_array('a', $allowedTags)) {
            $links = [];
            $i = 1;
            $data = str_replace('%', '%%', $data);
            $regexp = "#(?J)<a"
                ."(?:(?:\s+(?:(?:href\s*=\s*(['\"])(?<link>.*?)\\1\s*)|(?:\S+\s*=\s*(['\"])(.*?)\\3)\s*)*)|>)"
                .">?(?:(?:(?<text>.*?)(?:<\/a\s*>?|(?=<\w))|(?<text>.*)))#si";
            while (preg_match($regexp, $data, $matches)) {
                $text = '';
                if (!empty($matches['text'])) {
                    $text = str_replace('%%', '%', $matches['text']);
                }
                $url = $this->filterUrl($matches['link'] ?? '');
                //Recreate a minimalistic secure a tag
                $links[] = sprintf(
                    '<a href="%s">%s</a>',
                    htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false),
                    $this->escaper->escapeHtml($text)
                );
                $data = str_replace($matches[0], '%' . $i . '$s', $data);
                ++$i;
            }
            $data = $this->escaper->escapeHtml($data, $allowedTags);
            return vsprintf($data, $links);
        }
        return $this->escaper->escapeHtml($data, $allowedTags);
    }

    /**
     * Filter the URL for allowed protocols.
     *
     * @param string $url
     * @return string
     */
    private function filterUrl(string $url): string
    {
        if ($url) {
            //Revert the sprintf escaping
            $url = str_replace('%%', '%', $url);
            $urlScheme = parse_url($url, PHP_URL_SCHEME);
            $urlScheme = $urlScheme ? strtolower($urlScheme) : '';
            if ($urlScheme !== 'http' && $urlScheme !== 'https') {
                $url = null;
            }
        }

        if (!$url) {
            $url = '#';
        }

        return $url;
    }
}
