<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Resolver;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SendFriend\Model\SendFriend;
use Magento\SendFriend\Model\SendFriendFactory;
use Magento\SendFriendGraphQl\Model\Resolver\ProductService;

/**
 * @inheritdoc
 */
class SendEmailToFriend implements ResolverInterface
{
    /**
     * @var SendFriendFactory
     */
    private $sendFriendFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * @param SendFriendFactory $sendFriendFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param ManagerInterface $eventManager
     * @param ProductService $productService
     */
    public function __construct(
        SendFriendFactory $sendFriendFactory,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $eventManager,
        ProductService $productService
    ) {
        $this->sendFriendFactory = $sendFriendFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
        $this->productService = $productService;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        if ($sendFriend->getMaxSendsToFriend() && $sendFriend->isExceedLimit()) {
            throw new GraphQlInputException(
                __('You can\'t send messages more than %1 times an hour.', $sendFriend->getMaxSendsToFriend())
            );
        }

        $product = $this->productService->getProduct($args['input']['product_id']);
        $this->eventManager->dispatch('sendfriend_product', ['product' => $product]);
        $sendFriend->setProduct($product);

        $senderData = $this->extractSenderData($args);
        $sendFriend->setSender($senderData);

        $recipientsData = $this->extractRecipientsData($args);
        $sendFriend->setRecipients($recipientsData);

        $this->validateSendFriendModel($sendFriend, $senderData, $recipientsData);
        $sendFriend->send();

        return array_merge($senderData, $recipientsData);
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

    /**
     * Extract recipients data
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    private function extractRecipientsData(array $args): array
    {
        $recipients = [];
        foreach ($args['input']['recipients'] as $recipient) {
            if (empty($recipient['name'])) {
                throw new GraphQlInputException(__('Please provide Name for all of recipients.'));
            }

            if (empty($recipient['email'])) {
                throw new GraphQlInputException(__('Please provide Email for all of recipients.'));
            }

            $recipients[] = [
                'name' => $recipient['name'],
                'email' => $recipient['email'],
            ];
        }
        return ['recipients' => $recipients];
    }

    /**
     * Extract sender data
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    private function extractSenderData(array $args): array
    {
        if (empty($args['input']['sender']['name'])) {
            throw new GraphQlInputException(__('Please provide Name of sender.'));
        }

        if (empty($args['input']['sender']['email'])) {
            throw new GraphQlInputException(__('Please provide Email of sender.'));
        }

        if (empty($args['input']['sender']['message'])) {
            throw new GraphQlInputException(__('Please provide Message.'));
        }

        return [
            'sender' => [
                'name' => $args['input']['sender']['name'],
                'email' => $args['input']['sender']['email'],
                'message' => $args['input']['sender']['message'],
            ],
        ];
    }
}
