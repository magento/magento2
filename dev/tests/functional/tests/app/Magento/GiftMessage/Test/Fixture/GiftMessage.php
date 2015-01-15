<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class GiftMessage
 * Fixture for gift message
 */
class GiftMessage extends InjectableFixture
{
    /**
     * Path to GiftMessage repository
     *
     * @var string
     */
    protected $repositoryClass = 'Magento\GiftMessage\Test\Repository\GiftMessage';

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

    protected $items = [
        'attribute_code' => 'items',
        'backend_type' => 'virtual',
        'source' => 'Magento\GiftMessage\Test\Fixture\GiftMessage\Items',
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

    public function getItems()
    {
        return $this->getData('items');
    }
}
