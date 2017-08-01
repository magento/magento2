<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @deprecated 2.1.0
 * @since 2.0.0
 */
class Sidebar
{
    /**
     * @var Cart
     * @since 2.0.0
     */
    protected $cart;

    /**
     * @var HelperData
     * @since 2.0.0
     */
    protected $helperData;

    /**
     * @var ResolverInterface
     * @since 2.0.0
     */
    protected $resolver;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $summaryQty;

    /**
     * @param Cart $cart
     * @param HelperData $helperData
     * @param ResolverInterface $resolver
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getSummaryText()
    {
        return ($this->getSummaryQty() == 1) ? __(' item') : __(' items');
    }

    /**
     * Retrieve subtotal block html
     *
     * @return string
     * @since 2.0.0
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
