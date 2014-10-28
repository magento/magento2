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

namespace Magento\GiftMessage\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class GiftMessage
 * Fixture for gift message
 */
class GiftMessage extends InjectableFixture
{
    protected $defaultDataSet = [
        'allow_gift_options' => 'Yes',
        'allow_gift_messages_for_order' => 'Yes',
        'sender' => 'John Doe',
        'recipient' => 'Jane Doe',
        'message' => 'text_%isolation%',
    ];

    protected $gift_message_id = [
        'attribute_code' => 'gift_message_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_id = [
        'attribute_code' => 'customer_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $sender = [
        'attribute_code' => 'sender',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $recipient = [
        'attribute_code' => 'recipient',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $message = [
        'attribute_code' => 'message',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $allow_gift_options = [
        'attribute_code' => 'allow_gift_options',
        'backend_type' => 'virtual',
    ];

    protected $allow_gift_messages_for_order = [
        'attribute_code' => 'allow_gift_messages_for_order',
        'backend_type' => 'virtual',
    ];

    protected $allow_gift_options_for_items = [
        'attribute_code' => 'allow_gift_options_for_items',
        'backend_type' => 'virtual',
    ];

    public function getGiftMessageId()
    {
        return $this->getData('gift_message_id');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function getSender()
    {
        return $this->getData('sender');
    }

    public function getRecipient()
    {
        return $this->getData('recipient');
    }

    public function getMessage()
    {
        return $this->getData('message');
    }

    public function getAllowGiftMessagesForOrder()
    {
        return $this->getData('allow_gift_messages_for_order');
    }

    public function getAllowGiftOptionsForItems()
    {
        return $this->getData('allow_gift_options_for_items');
    }

    public function getAllowGiftOptions()
    {
        return $this->getData('allow_gift_options');
    }
}
