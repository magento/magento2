<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Newsletter\Model\GuestSubscriptionChecker;
use Psr\Log\LoggerInterface;

/**
 * Newsletter subscription status verification controller.
 */
class Status extends Action implements HttpGetActionInterface
{
    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    /**
     * @var GuestSubscriptionChecker
     */
    private $guestSubscriptionChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param EmailAddressValidator $emailAddressValidator
     * @param GuestSubscriptionChecker $guestSubscriptionChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        EmailAddressValidator $emailAddressValidator,
        GuestSubscriptionChecker $guestSubscriptionChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->emailAddressValidator = $emailAddressValidator;
        $this->guestSubscriptionChecker = $guestSubscriptionChecker;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');

        $response = [
            'subscribed' => false,
            'errors' => false,
        ];
        try {
            if (!empty($email) && $this->emailAddressValidator->isValid($email)) {
                $response['subscribed'] = $this->guestSubscriptionChecker->isSubscribed($email);
            }
        } catch (LocalizedException | \DomainException $exception) {
            $this->logger->error($exception->getMessage());
            $response['errors'] = true;
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
