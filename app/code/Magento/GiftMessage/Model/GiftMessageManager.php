<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\Framework\Exception\CouldNotSaveException;

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
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                    )->setCustomerId(
                        $quote->getCustomerId()
                    )->save();

                    $entity->setGiftMessageId($giftMessage->getId())->save();
                } catch (\Exception $e) {
                }
            }
        }
        return $this;
    }

    /**
     * Sets the gift message to item or quote.
     *
     * @param \Magento\Quote\Model\Quote $quote The quote.
     * @param string $type The type.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @param null|int $entityId The entity ID.
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException The gift message isn't available.
     */
    public function setMessage(\Magento\Quote\Model\Quote $quote, $type, $giftMessage, $entityId = null)
    {
        $message[$type][$entityId] = [
            'from' => $giftMessage->getSender(),
            'to' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];

        try {
            $this->add($message, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The gift message couldn't be added to Cart."));
        }
    }
}
