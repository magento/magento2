<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Transparent;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class for redirecting the Paypal response result to Magento controller.
 */
class Redirect extends AbstractAction
{
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
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param Transparent $transparent
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        Transparent $transparent,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->transparent = $transparent;
        $this->logger = $logger;
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $gatewayResponse = (array)$this->getRequest()->getPostValue();
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
