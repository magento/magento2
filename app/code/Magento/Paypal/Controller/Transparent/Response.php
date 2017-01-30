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
use Magento\Paypal\Model\Payflow\Transparent;

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
     * @var LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Transaction $transaction
     * @param ResponseValidator $responseValidator
     * @param LayoutFactory $resultLayoutFactory
     * @param Transparent $transparent
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Transaction $transaction,
        ResponseValidator $responseValidator,
        LayoutFactory $resultLayoutFactory,
        Transparent $transparent
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->transaction = $transaction;
        $this->responseValidator = $responseValidator;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->transparent = $transparent;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $parameters = [];
        try {
            $response = $this->transaction->getResponseObject($this->getRequest()->getPostValue());
            $this->responseValidator->validate($response, $this->transparent);
            $this->transaction->savePaymentInQuote($response);
        } catch (LocalizedException $exception) {
            $parameters['error'] = true;
            $parameters['error_msg'] = $exception->getMessage();
        }

        $this->_objectManager->get(\Magento\Payment\Model\IframeService::class)->setParams($parameters);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load(['transparent_payment_response']);

        return $resultLayout;
    }
}
