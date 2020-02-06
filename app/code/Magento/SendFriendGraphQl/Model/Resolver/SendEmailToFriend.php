<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\SendFriend\Helper\Data as SendFriendHelper;
use Magento\SendFriendGraphQl\Model\SendFriend\SendEmail;

/**
 * @inheritdoc
 */
class SendEmailToFriend implements ResolverInterface
{
    /**
     * @var SendFriendHelper
     */
    private $sendFriendHelper;

    /**
     * @var SendEmail
     */
    private $sendEmail;

    /**
     * @param SendEmail $sendEmail
     * @param SendFriendHelper $sendFriendHelper
     */
    public function __construct(
        SendEmail $sendEmail,
        SendFriendHelper $sendFriendHelper
    ) {
        $this->sendEmail = $sendEmail;
        $this->sendFriendHelper = $sendFriendHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (!$this->sendFriendHelper->isAllowForGuest()
            && false === $context->getExtensionAttributes()->getIsCustomer()
        ) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $senderData = $this->extractSenderData($args);
        $recipientsData = $this->extractRecipientsData($args);

        $this->sendEmail->execute(
            $args['input']['product_id'],
            $senderData,
            $recipientsData
        );
        return array_merge($senderData, $recipientsData);
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
