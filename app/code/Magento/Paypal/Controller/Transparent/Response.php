<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Transparent;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Transparent\Iframe;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Framework\Session\Generic as Session;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class for requesting the response result form the paypal controller.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Response extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * @var PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * @var Session
     */
    private $sessionTransparent;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Transaction $transaction
     * @param ResponseValidator $responseValidator
     * @param LayoutFactory $resultLayoutFactory
     * @param Transparent $transparent
     * @param Session|null $sessionTransparent
     * @param PaymentFailuresInterface|null $paymentFailures
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Transaction $transaction,
        ResponseValidator $responseValidator,
        LayoutFactory $resultLayoutFactory,
        Transparent $transparent,
        Session $sessionTransparent = null,
        PaymentFailuresInterface $paymentFailures = null
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->transaction = $transaction;
        $this->responseValidator = $responseValidator;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->transparent = $transparent;
        $this->sessionTransparent = $sessionTransparent ?: $this->_objectManager->get(Session::class);
        $this->paymentFailures = $paymentFailures ?: $this->_objectManager->get(PaymentFailuresInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Saves the payment in quote
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $parameters = [];
        try {
            $response = $this->transaction->getResponseObject($this->getRequest()->getPostValue());
            $this->responseValidator->validate($response, $this->transparent);
            $this->transaction->savePaymentInQuote($response, (int)$this->sessionTransparent->getQuoteId());
        } catch (LocalizedException $exception) {
            $parameters['error'] = true;
            $parameters['error_msg'] = $exception->getMessage();
            $this->paymentFailures->handle((int)$this->sessionTransparent->getQuoteId(), $parameters['error_msg']);
        }

        $this->coreRegistry->register(Iframe::REGISTRY_KEY, $parameters);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load(['transparent_payment_response']);

        return $resultLayout;
    }
}
