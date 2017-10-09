<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

use Magento\Sales\Model\Order\Item;

class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderItemIds = $this->getRequest()->getPost('order_items');
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class)
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection \Magento\Sales\Model\ResourceModel\Order\Item\Collection */
            foreach ($itemsCollection as $item) {
                try {
                    $this->addOrderItem($item);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    return $this->_goBack();
                }
            }
            $this->cart->save();
        }
        return $this->_goBack();
    }

    /**
     * Add item to cart.
     *
     * Add item to cart only if it's belongs to customer.
     *
     * @param Item $item
     * @return void
     */
    private function addOrderItem(Item $item)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = $this->cart->getCustomerSession();
        if ($session->isLoggedIn()) {
            $orderCustomerId = $item->getOrder()->getCustomerId();
            $currentCustomerId = $session->getCustomer()->getId();
            if ($orderCustomerId == $currentCustomerId) {
                $this->cart->addOrderItem($item, 1);
            }
        }
    }
}
