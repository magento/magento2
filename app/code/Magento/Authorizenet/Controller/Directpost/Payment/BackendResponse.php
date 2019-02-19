<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Authorizenet\Helper\DataFactory;
use Magento\Authorizenet\Model\Directpost;
use Magento\Authorizenet\Model\DirectpostFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class BackendResponse extends \Magento\Authorizenet\Controller\Directpost\Payment implements CsrfAwareActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DirectpostFactory
     */
    private $directpostFactory;

    /**
     * BackendResponse constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataFactory $dataFactory
     * @param DirectpostFactory $directpostFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataFactory $dataFactory,
        DirectpostFactory $directpostFactory,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $coreRegistry, $dataFactory);
        $this->directpostFactory = $directpostFactory ?: $this->_objectManager->create(DirectpostFactory::class);
        $this->logger = $logger ?: $this->_objectManager->get(LoggerInterface::class);
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
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var Directpost $paymentMethod */
        $paymentMethod = $this->directpostFactory->create();
        if (!empty($data['store_id'])) {
            $paymentMethod->setStore($data['store_id']);
        }
        $paymentMethod->setResponseData($data);
        try {
            $paymentMethod->validateResponse();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            return $this->_redirect('noroute');
        }
        $this->_responseAction('adminhtml');
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
