<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Controller\Adminhtml\OAuth;

use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\AdobeImsApi\Api\LogInInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\User\Api\Data\UserInterface;
use Psr\Log\LoggerInterface;

/**
 * Callback action for managing user authentication with the Adobe services
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Callback extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_AdobeIms::login';

    /**
     * Constants of response
     *
     * RESPONSE_TEMPLATE - template of response
     * RESPONSE_SUCCESS_CODE success code
     * RESPONSE_ERROR_CODE error code
     */
    private const RESPONSE_TEMPLATE = 'auth[code=%s;message=%s]';
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';

    /**
     * Constants of request
     *
     * REQUEST_PARAM_ERROR error
     * REQUEST_PARAM_CODE code
     */
    private const REQUEST_PARAM_ERROR = 'error';
    private const REQUEST_PARAM_CODE = 'code';

    /**
     * @var GetTokenInterface
     */
    private $getToken;

    /**
     * @var LogInInterface
     */
    private $login;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param GetTokenInterface $getToken
     * @param LogInInterface $login
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        GetTokenInterface $getToken,
        LogInInterface $login,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->getToken = $getToken;
        $this->login = $login;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        try {
            $this->validateCallbackRequest();
            $tokenResponse = $this->getToken->execute(
                (string)$this->getRequest()->getParam(self::REQUEST_PARAM_CODE)
            );
            $this->login->execute((int) $this->getUser()->getId(), $tokenResponse);

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_SUCCESS_CODE,
                __('Authorization was successful')
            );
        } catch (AuthorizationException $exception) {
            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                __(
                    'Login failed. Please check if <a href="%url">the Secret Key</a> is set correctly and try again.',
                    [
                        'url' => $this->getUrl(
                            'adminhtml/system_config/edit',
                            [
                                'section' => 'system',
                                '_fragment' => 'system_adobe_stock_integration-link'
                            ]
                        )
                    ]
                )
            );
        } catch (ConfigurationMismatchException | CouldNotSaveException $exception) {
            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                $exception->getMessage()
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                __('Something went wrong.')
            );
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($response);

        return $resultRaw;
    }

    /**
     * Validate callback request from the Adobe OAth service
     *
     * @throws ConfigurationMismatchException
     */
    private function validateCallbackRequest(): void
    {
        $error = $this->getRequest()->getParam(self::REQUEST_PARAM_ERROR);
        if ($error) {
            $message = __(
                'An error occurred during the callback request from the Adobe service: %error',
                ['error' => $error]
            );
            throw new ConfigurationMismatchException($message);
        }
    }

    /**
     * Get Authorised User
     *
     * @return UserInterface
     */
    private function getUser(): UserInterface
    {
        if (!$this->_auth->getUser() instanceof UserInterface) {
            throw new \RuntimeException('Auth user object must be an instance of UserInterface');
        }

        return $this->_auth->getUser();
    }
}
