<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Transparent;

use Magento\Framework\Registry;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Transparent\Iframe;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;

/**
 * Class Response
 */
class Response extends \Magento\Framework\App\Action\Action
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
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Transaction $transaction
     * @param ResponseValidator $responseValidator
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Transaction $transaction,
        ResponseValidator $responseValidator,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->transaction = $transaction;
        $this->responseValidator = $responseValidator;
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $parameters = [];
        try {
            $response = $this->transaction->getResponseObject($this->getRequest()->getPostValue());
            $this->responseValidator->validate($response);
            $this->transaction->savePaymentInQuote($response);
        } catch (LocalizedException $exception) {
            $parameters['error'] = true;
            $parameters['error_msg'] = $exception->getMessage();
        }

        $this->coreRegistry->register(Iframe::REGISTRY_KEY, $parameters);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load(['transparent_payment_response']);

        return $resultLayout;
    }
}
