<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Authentication\Rest\OauthClient;

use OAuth\Common\Http\Uri\UriInterface;

/**
 * Signature class for Magento REST API.
 */
class Signature extends \OAuth\OAuth1\Signature\Signature
{
    /**
     * @inheritDoc
     *
     * In addition to the original method, allows array parameters for filters.
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
            $signatureData[rawurlencode($key)] = rawurlencode($value);
        }

        ksort($signatureData);

        // determine base uri
        $baseUri = $uri->getScheme() . '://' . $uri->getRawAuthority();

        if ('/' == $uri->getPath()) {
            $baseUri .= $uri->hasExplicitTrailingHostSlash() ? '/' : '';
        } else {
            $baseUri .= $uri->getPath();
        }

        $baseString = strtoupper($method) . '&';
        $baseString .= rawurlencode($baseUri) . '&';
        $baseString .= rawurlencode($this->buildSignatureDataString($signatureData));

        return base64_encode($this->hash($baseString));
    }

    /**
     * @inheritDoc
     */
    protected function hash($data)
    {
        switch (strtoupper($this->algorithm)) {
            case 'HMAC-SHA256':
                return hash_hmac('sha256', $data, $this->getSigningKey(), true);
            default:
                return parent::hash($data);
        }
    }
}
