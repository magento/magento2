<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Authentication\Rest\OauthClient;

use Magento\Framework\Oauth\Helper\Utility;

/**
 * Signature class for Magento REST API.
 */
class Signature
{
    /**
     * @param Utility $helper
     */
    public function __construct(private readonly Utility $helper)
    {
    }

    /**
     * Get the signature
     *
     * @param array $params
     * @param string $signatureMethod
     * @param string $consumerSecret
     * @param string|null $tokenSecret
     * @param string $httpMethod
     * @param string $requestUrl
     * @return string
     */
    public function getSignature(
        array $params,
        string $signatureMethod,
        string $consumerSecret,
        ?string $tokenSecret,
        string $httpMethod,
        string $requestUrl
    ): string {
        $data = parse_url($requestUrl);
        $queryStringData = !isset($data['query']) ? [] : array_reduce(
            explode('&', $data['query']),
            function ($carry, $item) {
                list($key, $value) = explode('=', $item, 2);
                $carry[rawurldecode($key)] = rawurldecode($value);
                return $carry;
            },
            []
        );

        return $this->helper->sign(
            array_merge($queryStringData, $params),
            $signatureMethod,
            $consumerSecret,
            $tokenSecret,
            $httpMethod,
            $requestUrl
        );
    }
}
