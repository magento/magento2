<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Ajax;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Newsletter\Model\GuestSubscriptionChecker;
use Psr\Log\LoggerInterface;

/**
 * Newsletter subscription status verification controller.
 */
class Status extends Action\Action implements Action\HttpGetActionInterface
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
     * @param Action\Context $context
     * @param EmailAddressValidator $emailAddressValidator
     * @param GuestSubscriptionChecker $guestSubscriptionChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
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
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $response['errors'] = true;
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
