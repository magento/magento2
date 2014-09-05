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
