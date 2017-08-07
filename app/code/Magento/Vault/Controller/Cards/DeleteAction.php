<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Controller\CardsManagement;
use Magento\Vault\Model\PaymentTokenManagement;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class DeleteAction extends CardsManagement
{
    const WRONG_REQUEST = 1;

    const WRONG_TOKEN = 2;

    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     * @since 2.1.0
     */
    private $errorsMap = [];

    /**
     * @var JsonFactory
     * @since 2.1.0
     */
    private $jsonFactory;

    /**
     * @var Validator
     * @since 2.1.0
     */
    private $fkValidator;

    /**
     * @var PaymentTokenRepositoryInterface
     * @since 2.1.0
     */
    private $tokenRepository;

    /**
     * @var PaymentTokenManagement
     * @since 2.1.0
     */
    private $paymentTokenManagement;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param PaymentTokenManagement $paymentTokenManagement
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        Validator $fkValidator,
        PaymentTokenRepositoryInterface $tokenRepository,
        PaymentTokenManagement $paymentTokenManagement
    ) {
        parent::__construct($context, $customerSession);
        $this->jsonFactory = $jsonFactory;
        $this->fkValidator = $fkValidator;
        $this->tokenRepository = $tokenRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     * @since 2.1.0
     */
    public function execute()
    {
        $request = $this->_request;
        if (!$request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $paymentToken = $this->getPaymentToken($request);
        if ($paymentToken === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {
            $this->tokenRepository->delete($paymentToken);
        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * @param int $errorCode
     * @return ResponseInterface
     * @since 2.1.0
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );

        return $this->_redirect('vault/cards/listaction');
    }

    /**
     * @return ResponseInterface
     * @since 2.1.0
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(
            __('Stored Payment Method was successfully removed')
        );
        return $this->_redirect('vault/cards/listaction');
    }

    /**
     * @param Http $request
     * @return PaymentTokenInterface|null
     * @since 2.1.0
     */
    private function getPaymentToken(Http $request)
    {
        $publicHash = $request->getPostValue(PaymentTokenInterface::PUBLIC_HASH);

        if ($publicHash === null) {
            return null;
        }

        return $this->paymentTokenManagement->getByPublicHash(
            $publicHash,
            $this->customerSession->getCustomerId()
        );
    }
}
