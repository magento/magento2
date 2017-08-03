<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;

/**
 * Stores access token to MBI that received in body.
 * @since 2.2.0
 */
class SignUp implements ResponseHandlerInterface
{
    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @var ConverterInterface
     * @since 2.2.0
     */
    private $converter;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param ConverterInterface $converter
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        ConverterInterface $converter
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->converter = $converter;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
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
