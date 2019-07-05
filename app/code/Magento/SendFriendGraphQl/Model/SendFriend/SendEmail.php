<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\SendFriend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\SendFriend\Model\SendFriend;
use Magento\SendFriend\Model\SendFriendFactory;
use Magento\SendFriendGraphQl\Model\Provider\GetVisibleProduct;

/**
 * Send Product Email to Friend(s)
 */
class SendEmail
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SendFriendFactory
     */
    private $sendFriendFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var GetVisibleProduct
     */
    private $visibleProductProvider;

    /**
     * SendEmail constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SendFriendFactory $sendFriendFactory
     * @param ManagerInterface $eventManager
     * @param GetVisibleProduct $visibleProductProvider
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ProductRepositoryInterface $productRepository,
        SendFriendFactory $sendFriendFactory,
        ManagerInterface $eventManager,
        GetVisibleProduct $visibleProductProvider
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productRepository = $productRepository;
        $this->sendFriendFactory = $sendFriendFactory;
        $this->eventManager = $eventManager;
        $this->visibleProductProvider = $visibleProductProvider;
    }

    /**
     * Send product email to friend(s)
     *
     * @param int $productId
     * @param array $senderData
     * @param array $recipientsData
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $productId, array $senderData, array $recipientsData): void
    {
        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        if ($sendFriend->getMaxSendsToFriend() && $sendFriend->isExceedLimit()) {
            throw new GraphQlInputException(
                __('You can\'t send messages more than %1 times an hour.', $sendFriend->getMaxSendsToFriend())
            );
        }

        $product = $this->visibleProductProvider->execute($productId);

        $this->eventManager->dispatch('sendfriend_product', ['product' => $product]);

        $sendFriend->setProduct($product);
        $sendFriend->setSender($senderData);
        $sendFriend->setRecipients($recipientsData);

        $this->validateSendFriendModel($sendFriend, $senderData, $recipientsData);

        $sendFriend->send();
    }

    /**
     * Validate send friend model
     *
     * @param SendFriend $sendFriend
     * @param array $senderData
     * @param array $recipientsData
     * @return void
     * @throws GraphQlInputException
     */
    private function validateSendFriendModel(SendFriend $sendFriend, array $senderData, array $recipientsData): void
    {
        $sender = $this->dataObjectFactory->create()->setData($senderData['sender']);
        $sendFriend->setData('_sender', $sender);

        $emails = array_column($recipientsData['recipients'], 'email');
        $recipients = $this->dataObjectFactory->create()->setData('emails', $emails);
        $sendFriend->setData('_recipients', $recipients);

        $validationResult = $sendFriend->validate();
        if ($validationResult !== true) {
            throw new GraphQlInputException(__(implode($validationResult)));
        }
    }
}
