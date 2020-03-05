<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Transparent;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class for redirecting the Paypal response result to Magento controller.
 */
class Redirect implements ActionInterface, CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param LayoutFactory $resultLayoutFactory
     * @param Transparent $transparent
     * @param Logger $logger
     */
    public function __construct(
        RequestInterface $request,
        LayoutFactory $resultLayoutFactory,
        Transparent $transparent,
        Logger $logger
    ) {
        $this->request = $request;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->transparent = $transparent;
        $this->logger = $logger;
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
     * @throws LocalizedException
     */
    public function execute()
    {
        $gatewayResponse = (array)$this->request->getPostValue();
        $this->logger->debug(
            ['PayPal PayflowPro redirect:' => $gatewayResponse],
            $this->transparent->getDebugReplacePrivateDataKeys(),
            $this->transparent->getDebugFlag()
        );

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load(['transparent_payment_redirect']);

        return $resultLayout;
    }
}
