<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SendFriend\Model\SendFriend;
use Magento\SendFriend\Model\SendFriendFactory;
use Magento\SendFriend\Helper\Data as SendFriendHelper;

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var SendFriendHelper
     */
    private $sendFriendHelper;

    /**
     * @param SendFriendFactory $sendFriendFactory
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param ManagerInterface $eventManager
     * @param SendFriendHelper|null $sendFriendHelper
     */
    public function __construct(
        SendFriendFactory $sendFriendFactory,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $eventManager,
        SendFriendHelper $sendFriendHelper = null
    ) {
        $this->sendFriendFactory = $sendFriendFactory;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
        $this->sendFriendHelper = $sendFriendHelper ?? ObjectManager::getInstance()->get(SendFriendHelper::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->sendFriendHelper->isAllowForGuest() && $this->isUserGuest($context->getUserId(), $context->getUserType())) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        if ($sendFriend->getMaxSendsToFriend() && $sendFriend->isExceedLimit()) {
            throw new GraphQlInputException(
                __('You can\'t send messages more than %1 times an hour.', $sendFriend->getMaxSendsToFriend())
            );
        }

        $product = $this->getProduct($args['input']['product_id']);
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
     * Get product
     *
     * @param int $productId
     * @return ProductInterface
     * @throws GraphQlNoSuchEntityException
     */
    private function getProduct(int $productId): ProductInterface
    {
        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                throw new GraphQlNoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $product;
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

    /**
     * Checking if current customer is guest
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isUserGuest(?int $customerId, ?int $customerType): bool
    {
        if (null === $customerId || null === $customerType) {
            return true;
        }
        return 0 === (int)$customerId || (int)$customerType === UserContextInterface::USER_TYPE_GUEST;
    }
}
