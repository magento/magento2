<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Controller\Cart;

class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return void
     */
    public function execute()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
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
                    $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                    $this->_goBack();
                }
            }
            $this->cart->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        }
        $this->_goBack();
    }
}
