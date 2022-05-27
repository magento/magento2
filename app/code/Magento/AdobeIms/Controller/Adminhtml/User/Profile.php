<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Controller\Adminhtml\User;

use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Get Adobe services user account action
 */
class Profile extends Action implements HttpGetActionInterface
{
    /**
     * Successful result code.
     */
    private const HTTP_OK = 200;

    /**
     * Internal server error response code.
     */
    private const HTTP_INTERNAL_ERROR = 500;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdobeIms::login';

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var UserProfileRepositoryInterface
     */
    private $userProfileRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Profile constructor.
     *
     * @param Action\Context $context
     * @param UserContextInterface $userContext
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        UserContextInterface $userContext,
        UserProfileRepositoryInterface $userProfileRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->userProfileRepository = $userProfileRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $userProfile = $this->userProfileRepository->getByUserId((int)$this->userContext->getUserId());
            $userData = [
                'email' => $userProfile->getEmail(),
                'name' => $userProfile->getName(),
                'image' => $userProfile->getImage()
            ];
            $responseCode = self::HTTP_OK;

            $responseContent = [
                'success' => true,
                'error_message' => '',
                'result' => $userData
            ];

        } catch (NoSuchEntityException $exception) {
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $this->logger->critical($exception);
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred during get user data. Contact support.'),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
