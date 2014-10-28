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

namespace Magento\GiftMessage\Test\Block\Message\Order;

/**
 * Class View
 * Gift message block for order on order view page
 */
class View extends \Magento\Sales\Test\Block\Order\View
{
    /**
     * Gift message sender selector
     *
     * @var string
     */
    protected $giftMessageSenderSelector = ".gift-sender";

    /**
     * Gift message recipient selector
     *
     * @var string
     */
    protected $giftMessageRecipientSelector = ".gift-recipient";

    /**
     * Gift message text selector
     *
     * @var string
     */
    protected $giftMessageTextSelector = ".gift-message-text";

    /**
     * Get gift message for order
     *
     * @return array
     */
    public function getGiftMessage()
    {
        $message = [];

        $message['sender'] = $this->_rootElement->find($this->giftMessageSenderSelector)->getText();
        $message['recipient'] = $this->_rootElement->find($this->giftMessageRecipientSelector)->getText();
        $message['message'] = $this->_rootElement->find($this->giftMessageTextSelector)->getText();
        $message = preg_replace('@.*?:\s(.*)@', '\1', $message);

        return $message;
    }
}
