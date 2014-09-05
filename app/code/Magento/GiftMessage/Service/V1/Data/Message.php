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
namespace Magento\GiftMessage\Service\V1\Data;

/**
 * Gift Message data object
 *
 * @codeCoverageIgnore
 */
class Message extends \Magento\Framework\Service\Data\AbstractSimpleObject
{
    const GIFT_MESSAGE_ID = 'gift_message_id';

    const SENDER = 'sender';

    const RECIPIENT = 'recipient';

    const MESSAGE = 'message';

    const CUSTOMER_ID = 'customer_id';

    /**
     * Get gift message id
     *
     * @return int|null
     */
    public function getGiftMessageId()
    {
        return $this->_get(self::GIFT_MESSAGE_ID);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Sender name
     *
     * @return string
     */
    public function getSender()
    {
        return $this->_get(self::SENDER);
    }

    /**
     * Recipient name
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->_get(self::RECIPIENT);
    }

    /**
     * Message text
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }
}
