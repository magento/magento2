<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Controller\Ipn;

use Magento\Framework\Exception\RemoteServiceUnavailableException;

/**
 * Unified IPN controller for all supported PayPal methods
 * @since 2.0.0
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\IpnFactory
     * @since 2.0.0
     */
    protected $_ipnFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Paypal\Model\IpnFactory $ipnFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Paypal\Model\IpnFactory $ipnFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_ipnFactory = $ipnFactory;
        parent::__construct($context);
    }

    /**
     * Instantiate IPN model and pass IPN request to it
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.0.0
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = $this->getRequest()->getPostValue();
            $this->_ipnFactory->create(['data' => $data])->processIpnRequest();
        } catch (RemoteServiceUnavailableException $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setStatusHeader(503, '1.1', 'Service Unavailable')->sendResponse();
            /** @todo eliminate usage of exit statement */
            exit;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
