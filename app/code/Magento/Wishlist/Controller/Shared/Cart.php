<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

class Cart extends \Magento\Framework\App\Action\Action
{
    /**
     * Add shared wishlist item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item');

        /* @var $item \Magento\Wishlist\Model\Item */
        $item = $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId);

        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');

        $redirectUrl = $this->_redirect->getRefererUrl();

        try {
            $options = $this->_objectManager->create(
                'Magento\Wishlist\Model\Item\Option'
            )->getCollection()->addItemFilter(
                [$itemId]
            );
            $item->setOptions($options->getOptionsByItem($itemId));

            $item->addToCart($cart);
            $cart->save()->getQuote()->collectTotals();

            if ($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getShouldRedirectToCart()) {
                $redirectUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            if ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_NOT_SALABLE) {
                $this->messageManager->addError(__('This product(s) is out of stock.'));
            } else {
                $this->messageManager->addNotice($e->getMessage());
                $redirectUrl = $item->getProductUrl();
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot add item to shopping cart'));
        }

        return $this->getResponse()->setRedirect($redirectUrl);
    }
}
