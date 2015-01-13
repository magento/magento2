<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

class GiftMessageManager
{
    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @param MessageFactory $messageFactory
     */
    public function __construct(
        \Magento\GiftMessage\Model\MessageFactory $messageFactory
    ) {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $giftMessages
     * @param \Magento\Sales\Model\Quote $quote
     * @return $this
     */
    public function add($giftMessages, $quote)
    {
        if (!is_array($giftMessages)) {
            return $this;
        }
        // types are 'quote', 'quote_item', etc
        foreach ($giftMessages as $type => $giftMessageEntities) {
            foreach ($giftMessageEntities as $entityId => $message) {
                $giftMessage = $this->messageFactory->create();
                switch ($type) {
                    case 'quote':
                        $entity = $quote;
                        break;
                    case 'quote_item':
                        $entity = $quote->getItemById($entityId);
                        break;
                    case 'quote_address':
                        $entity = $quote->getAddressById($entityId);
                        break;
                    case 'quote_address_item':
                        $entity = $quote->getAddressById($message['address'])->getItemById($entityId);
                        break;
                    default:
                        $entity = $quote;
                        break;
                }

                if ($entity->getGiftMessageId()) {
                    $giftMessage->load($entity->getGiftMessageId());
                }

                if (trim($message['message']) == '') {
                    if ($giftMessage->getId()) {
                        try {
                            $giftMessage->delete();
                            $entity->setGiftMessageId(0)->save();
                        } catch (\Exception $e) {
                        }
                    }
                    continue;
                }

                try {
                    $giftMessage->setSender(
                        $message['from']
                    )->setRecipient(
                        $message['to']
                    )->setMessage(
                        $message['message']
                    )->save();

                    $entity->setGiftMessageId($giftMessage->getId())->save();
                } catch (\Exception $e) {
                }
            }
        }
        return $this;
    }
}
