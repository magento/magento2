<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Ipn;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Sales\Model\OrderFactory;

/**
 * Unified IPN controller for all supported PayPal methods
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\IpnFactory
     */
    protected $_ipnFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Paypal\Model\IpnFactory $ipnFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Paypal\Model\IpnFactory $ipnFactory,
        \Psr\Log\LoggerInterface $logger,
        ?OrderFactory $orderFactory = null
    ) {
        $this->_logger = $logger;
        $this->_ipnFactory = $ipnFactory;
        $this->orderFactory = $orderFactory ?: ObjectManager::getInstance()->get(OrderFactory::class);
        parent::__construct($context);
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
     * Instantiate IPN model and pass IPN request to it
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = $this->getRequest()->getPostValue();
            $this->_ipnFactory->create(['data' => $data])->processIpnRequest();
            $incrementId = $this->getRequest()->getPostValue()['invoice'];
            $this->_eventManager->dispatch(
                'paypal_checkout_success',
                [
                    'order' => $this->orderFactory->create()->loadByIncrementId($incrementId)
                ]
            );
        } catch (RemoteServiceUnavailableException $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setStatusHeader(503, '1.1', 'Service Unavailable')->sendResponse();
            /** @todo eliminate usage of exit statement */
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
