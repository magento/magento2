<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Authentication\Rest\OauthClient;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Uri\UriInterface;
use Magento\Framework\Oauth\Helper\Utility;

/**
 * Signature class for Magento REST API.
 */
class Signature extends \OAuth\OAuth1\Signature\Signature
{
    /**
     * @param Utility $helper
     * @param CredentialsInterface $credentials
     */
    public function __construct(private readonly Utility $helper, CredentialsInterface $credentials)
    {
        parent::__construct($credentials);
    }

    /**
     * @inheritDoc
     *
     * In addition to the original method, allows array parameters for filters
     * and matches validation signature algorithm
     */
    public function getSignature(UriInterface $uri, array $params, $method = 'POST')
    {
        $queryStringData = !$uri->getQuery() ? [] : array_reduce(
            explode('&', $uri->getQuery()),
            function ($carry, $item) {
                list($key, $value) = explode('=', $item, 2);
                $carry[rawurldecode($key)] = rawurldecode($value);
                return $carry;
            },
            []
        );

        $signatureData = [];
        foreach (array_merge($queryStringData, $params) as $key => $value) {
            $signatureData[rawurldecode($key)] = rawurlencode($value);
        }

        return $this->helper->sign(
            $signatureData,
            $this->algorithm,
            $this->credentials->getConsumerSecret(),
            $this->tokenSecret,
            $method,
            (string) $uri
        );
    }
}
