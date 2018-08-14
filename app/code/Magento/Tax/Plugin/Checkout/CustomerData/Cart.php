<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Plugin\Checkout\CustomerData;

class Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var array|null
     */
    protected $totals = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['subtotal_incl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalInclTax());
        $result['subtotal_excl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalExclTax());

        $items =$this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $this->itemPriceRenderer->setItem($item);
                    $this->itemPriceRenderer->setTemplate('checkout/cart/item/price/sidebar.phtml');
                    $result['items'][$key]['product_price']=$this->itemPriceRenderer->toHtml();
                }
            }
        }
        return $result;
    }

    /**
     * Get subtotal, including tax
     *
     * @return float
     */
    protected function getSubtotalInclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueInclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get subtotal, excluding tax
     *
     * @return float
     */
    protected function getSubtotalExclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueExclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        // TODO: TODO: MAGETWO-34824 duplicate \Magento\Checkout\CustomerData\Cart::getSectionData
        if (empty($this->totals)) {
            $this->totals = $this->getQuote()->getTotals();
        }
        return $this->totals;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Find item by id in items haystack
     *
     * @param int $id
     * @param array $itemsHaystack
     * @return \Magento\Quote\Model\Quote\Item | bool
     */
    protected function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}
