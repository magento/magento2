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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order;

use Magento\GiftMessage\Test\Fixture\GiftMessage;

/**
 * Class Create
 * Adminhtml GiftMessage order create block.
 */
class Create extends \Magento\Sales\Test\Block\Adminhtml\Order\Create
{
    /**
     * Sales order create items block.
     *
     * @var string
     */
    protected $itemsBlock = '#order-items_grid';

    /**
     * Fill order items gift messages.
     *
     * @param array $products
     * @param GiftMessage $giftMessage
     */
    public function fillGiftMessageForItems(array $products, GiftMessage $giftMessage)
    {
        // Click on rootElement to solve overlapping inner elements by header menu.
        $this->_rootElement->click();
        /** @var \Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Items $items */
        $items = $this->blockFactory->create(
            'Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Items',
            ['element' => $this->_rootElement->find($this->itemsBlock)]
        );
        foreach ($products as $key => $product) {
            $giftMessageItem = $giftMessage->getItems()[$key];
            $items->getItemProduct($product)->fillGiftMessageForm($giftMessageItem);
        }
    }
}
