<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Plugin;

use Magento\Quote\Model\Cart\TotalsAdditionalDataProcessor;
use Magento\Quote\Api\Data\TotalsAdditionalDataInterface;

/**
 * Shopping cart gift message item repository object.
 */
class TotalsDataProcessorPlugin
{
    /**
     * @var \Magento\GiftMessage\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\GiftMessage\Api\ItemRepositoryInterface
     */
    protected $itemRepositoryInterface;

    /**
     * @param \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        \Magento\GiftMessage\Api\CartRepositoryInterface $cartRepository,
        \Magento\GiftMessage\Api\ItemRepositoryInterface $itemRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->itemRepositoryInterface = $itemRepository;
    }

    /**
     * Set gift messages from additional data.
     *
     * @param \Magento\Quote\Model\Cart\TotalsAdditionalDataProcessor $subject
     * @param TotalsAdditionalDataInterface $additionalData
     * @param int $cartId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(
        TotalsAdditionalDataProcessor $subject,
        TotalsAdditionalDataInterface $additionalData,
        $cartId
    ) {
        $giftMessages = $additionalData->getExtensionAttributes()->getGiftMessages();
        foreach ($giftMessages as $giftMessage) {
            /** @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $entityType = $giftMessage->getExtensionAttributes()->getEntityType();
            $entityId = $giftMessage->getExtensionAttributes()->getEntityId();
            if ($entityType === 'quote') {
                $this->cartRepository->save($cartId, $giftMessage);
            } elseif ($entityType === 'item') {
                $this->itemRepositoryInterface->save($cartId, $giftMessage, $entityId);
            }
        }
    }
}
