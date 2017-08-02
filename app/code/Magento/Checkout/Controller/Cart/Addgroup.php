<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

/**
 * Class \Magento\Checkout\Controller\Cart\Addgroup
 *
 * @since 2.0.0
 */
class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', []);
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class)
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection \Magento\Sales\Model\ResourceModel\Order\Item\Collection */
            foreach ($itemsCollection as $item) {
                try {
                    $this->cart->addOrderItem($item, 1);
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
}
