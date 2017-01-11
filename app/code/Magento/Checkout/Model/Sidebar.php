<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Checkout\Helper\Data as HelperData;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * @deprecated
 */
class Sidebar
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var int
     */
    protected $summaryQty;

    /**
     * @param Cart $cart
     * @param HelperData $helperData
     * @param ResolverInterface $resolver
     * @codeCoverageIgnore
     */
    public function __construct(
        Cart $cart,
        HelperData $helperData,
        ResolverInterface $resolver
    ) {
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->resolver = $resolver;
    }

    /**
     * Compile response data
     *
     * @param string $error
     * @return array
     */
    public function getResponseData($error = '')
    {
        if (empty($error)) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }
        return $response;
    }

    /**
     * Check if required quote item exist
     *
     * @param int $itemId
     * @throws LocalizedException
     * @return $this
     */
    public function checkQuoteItem($itemId)
    {
        $item = $this->cart->getQuote()->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__('We can\'t find the quote item.'));
        }
        return $this;
    }

    /**
     * Remove quote item
     *
     * @param int $itemId
     * @return $this
     */
    public function removeQuoteItem($itemId)
    {
        $this->cart->removeItem($itemId);
        $this->cart->save();
        return $this;
    }

    /**
     * Update quote item
     *
     * @param int $itemId
     * @param int $itemQty
     * @throws LocalizedException
     * @return $this
     */
    public function updateQuoteItem($itemId, $itemQty)
    {
        $itemData = [$itemId => ['qty' => $this->normalize($itemQty)]];
        $this->cart->updateItems($itemData)->save();
        return $this;
    }

    /**
     * Apply normalization filter to item qty value
     *
     * @param int $itemQty
     * @return int|array
     */
    protected function normalize($itemQty)
    {
        if ($itemQty) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->resolver->getLocale()]
            );
            return $filter->filter($itemQty);
        }
        return $itemQty;
    }

    /**
     * Retrieve summary qty
     *
     * @return int
     */
    protected function getSummaryQty()
    {
        if (!$this->summaryQty) {
            $this->summaryQty = $this->cart->getSummaryQty();
        }
        return $this->summaryQty;
    }

    /**
     * Retrieve summary qty text
     *
     * @return string
     */
    protected function getSummaryText()
    {
        return ($this->getSummaryQty() == 1) ? __(' item') : __(' items');
    }

    /**
     * Retrieve subtotal block html
     *
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->cart->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;
        return $this->helperData->formatPrice($subtotal);
    }
}
