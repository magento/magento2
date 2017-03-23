<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class StoreTokenHandler
 */
class SignUp implements ResponseHandlerInterface
{
    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * SignUpResponseHandler constructor.
     *
     * @param AnalyticsToken $analyticsToken
     * @param LoggerInterface $logger
     * @param ConverterInterface $converter
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        LoggerInterface $logger,
        ConverterInterface $converter
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->logger = $logger;
        $this->converter = $converter;
    }

    /**
     * @inheritdoc
     */
    public function handleResponse(array $body)
    {
        if (isset($body['access-token']) && !empty($body['access-token'])) {
            $this->analyticsToken->storeToken($body['access-token']);
            return $body['access-token'];
        }

        return false;
    }
}
