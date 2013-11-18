<?php
/**
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GiftMessage\Model\Plugin;

class QuoteItem
{
    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_helper;

    /**
     * @param \Magento\GiftMessage\Helper\Message $helper
     */
    public function __construct(
        \Magento\GiftMessage\Helper\Message $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return \Magento\Sales\Model\Order\Item|mixed
     */
    public function aroundItemToOrderItem(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $invocationChain->proceed($arguments);
        $quoteItem = reset($arguments);

        $isAvailable = $this->_helper->isMessagesAvailable(
            'item',
            $quoteItem,
            $quoteItem->getStoreId()
        );

        $orderItem->setGiftMessageId($quoteItem->getGiftMessageId());
        $orderItem->setGiftMessageAvailable($isAvailable);
        return $orderItem;
    }
}
