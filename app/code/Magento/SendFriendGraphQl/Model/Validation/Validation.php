<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriendGraphQl\Model\Validation;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\SendFriend\Model\SendFriend;

class Validation
{
    /**
     * @var SendFriend
     */
    private $sendFriend;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        DataObjectFactory $dataObjectFactory,
        SendFriend $sendFriend
    ) {
        $this->sendFriend = $sendFriend;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param $args
     * @param array $recipientsArray
     * @throws GraphQlInputException
     */
    public function validate($args, array $recipientsArray): void
    {
        $this->prepareDataForSendFriendValidation($args, $recipientsArray);
        $validationResult = $this->sendFriend->validate();
        if ($validationResult !== true) {
            throw new GraphQlInputException(__(implode($validationResult)));
        }
        if ($this->sendFriend->getMaxSendsToFriend() && $this->sendFriend->isExceedLimit()) {
            throw new GraphQlInputException(__('You can\'t send messages more than %1 times an hour.',
                $this->sendFriend->getMaxSendsToFriend()
            ));
        }
    }

    private function prepareDataForSendFriendValidation(array $args, array $recipientsArray): void
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

}