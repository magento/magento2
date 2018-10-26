<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SendFriend\Model\SendFriend;

class SendEmailToFriend implements ResolverInterface
{
    /**
     * @var SendFriend
     */
    private $sendFriend;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        SendFriend $sendFriend,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->sendFriend = $sendFriend;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if ($this->sendFriend->getMaxSendsToFriend() && $this->sendFriend->isExceedLimit()) {
            throw new GraphQlInputException(__('You can\'t send messages more than %1 times an hour.',
                $this->sendFriend->getMaxSendsToFriend()
            ));
        }

        $product = $this->getProductFromRepository($args['input']['params']['product_id']);
        $senderArray = $this->getSenderArrayFromArgs($args);
        $recipientsArray = $this->getRecipientsArray($args);
        //@todo clarify if event should be dispatched
        //$this->_eventManager->dispatch('sendfriend_product', ['product' => $product]);
        $this->sendFriend->setSender($senderArray);
        $this->sendFriend->setRecipients($recipientsArray);
        $this->sendFriend->setProduct($product);

        $this->prepareDataForValidation($args, $recipientsArray);
        $validationResult = $this->sendFriend->validate();
        $this->addRecipientNameValidation($args);
        if ($validationResult !== true) {
            throw new GraphQlInputException(__(implode($validationResult)));
        }

        $this->sendFriend->send();

        return array_merge($senderArray, $recipientsArray);
    }

    private function prepareDataForValidation(array $args, array $recipientsArray): void
    {
        $sender = $this->dataObjectFactory->create()->setData([
            'name' => $args['input']['sender']['name'],
            'email'=> $args['input']['sender']['email'],
            'message' => $args['input']['sender']['message'],
        ]);
        $emails = [];
        foreach ($recipientsArray['recipients'] as $recipient) {
            $emails[] = $recipient['email'];
        }
        $recipients = $this->dataObjectFactory->create()->setData('emails', $emails);

        $this->sendFriend->setData('_sender', $sender);
        $this->sendFriend->setData('_recipients', $recipients);
    }

    /**
     * @param array $args
     * @throws GraphQlInputException
     */
    private function addRecipientNameValidation(array $args): void
    {
        foreach ($args['input']['recipients'] as $recipient) {
            if (empty($recipient['name'])) {
                throw new GraphQlInputException(
                    __('Please Provide Name for Recipient with this Email Address: ' . $recipient['email']
                ));
            }
        }
    }

    /**
     * @param int $productId
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProductFromRepository(int $productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                return false;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        return $product;
    }

    private function getRecipientsArray(array $args): array
    {
        $recipientsArray = [];
        foreach ($args['input']['recipients'] as $recipient) {
            $recipientsArray[] = [
                'name' => $recipient['name'],
                'email' => $recipient['email'],
            ];
        }
        return ['recipients' => $recipientsArray];
    }

    private function getSenderArrayFromArgs(array $args): array
    {
        return ['sender' => [
                    'name' => $args['input']['sender']['name'],
                    'email' => $args['input']['sender']['email'],
                    'message' => $args['input']['sender']['message'],
                    ]
            ];
    }
}
