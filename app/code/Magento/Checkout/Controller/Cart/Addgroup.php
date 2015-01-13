<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return void
     */
    public function execute()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', []);
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Item'
            )->getCollection()->addIdFilter(
                $orderItemIds
            )->load();
            /* @var $itemsCollection \Magento\Sales\Model\Resource\Order\Item\Collection */
            foreach ($itemsCollection as $item) {
                try {
                    $this->cart->addOrderItem($item, 1);
                } catch (\Magento\Framework\Model\Exception $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('We cannot add this item to your shopping cart'));
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->_goBack();
                }
            }
            $this->cart->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        }
        $this->_goBack();
    }
}
