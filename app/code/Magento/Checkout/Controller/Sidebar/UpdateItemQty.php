<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;

class UpdateItemQty extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var int
     */
    protected $summaryQty;

    /**
     * @return string
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = (int)$this->getRequest()->getParam('item_qty');

        try {
            $this->checkQuoteItem($itemId);
            $this->updateQuoteItem($itemId, $itemQty);
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
            $this->messageManager->addError(
                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
            );
            return $this->jsonResponse(false);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot update the shopping cart.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->jsonResponse(false);
        }
    }

    /**
     * Return response
     *
     * @param bool $success
     * @return $this
     */
    protected function jsonResponse($success = true)
    {
        $response = [
            'success' => $success,
            'data' => [
                'summary_qty' => $this->getSummaryQty(),
                'summary_text' => $this->getSummaryText(),
                'subtotal' => $this->getSubtotalHtml(),
            ],
        ];
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Check if required quote item exist
     *
     * @param int $itemId
     * @throws LocalizedException
     * @return $this
     */
    protected function checkQuoteItem($itemId)
    {
        $item = $this->cart->getQuote()->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__('We can\'t find the quote item.'));
        }
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
    protected function updateQuoteItem($itemId, $itemQty)
    {
        $item = $this->cart->updateItem($itemId, $this->normalize($itemQty));
        if (is_string($item)) {
            throw new LocalizedException(__($item));
        }
        if ($item->getHasError()) {
            throw new LocalizedException(__($item->getMessage()));
        }
        $this->cart->save();
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
                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
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
        $subtotal = isset($totals['subtotal']) ? $totals['subtotal']->getValue() : 0;

        return $this->_objectManager->get('Magento\Checkout\Helper\Data')
            ->formatPrice($subtotal);
    }
}
