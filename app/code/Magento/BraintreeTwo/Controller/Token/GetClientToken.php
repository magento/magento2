<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Controller\Token;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Webapi\Exception;
use Psr\Log\LoggerInterface;
use Magento\BraintreeTwo\Gateway\Config\Config;

/**
 * Class GetClientToken
 *
 */
class GetClientToken extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Config $config
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $controllerResult->setData(['client_token' => $this->config->getClientToken()]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->getErrorResponse($controllerResult);
        }

        return $controllerResult;
    }

    /**
     * @param ResultInterface $controllerResult
     * @return ResultInterface
     */
    private function getErrorResponse(ResultInterface $controllerResult)
    {
        $controllerResult->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $controllerResult->setData(['message' => __('Sorry, but something went wrong')]);

        return $controllerResult;
    }
}
