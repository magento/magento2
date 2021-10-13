<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Payload\ArbitraryPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Jwt\Payload\NestedPayload;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\JwtFrameworkAdapter\Model\Data\Claim;
use Magento\JwtFrameworkAdapter\Model\Data\Header;

/**
 * Create JWS data object.
 */
class JwsFactory
{
    public function create(
        array $protectedHeadersMap,
        string $payload,
        ?array $unprotectedHeadersMap
    ): JwsInterface {
        $protectedHeaders = [];
        foreach ($protectedHeadersMap as $header => $headerValue) {
            $protectedHeaders[] = new Header($header, $headerValue, null);
        }
        $publicHeaders = null;
        if ($unprotectedHeadersMap) {
            $publicHeaders = [];
            foreach ($unprotectedHeadersMap as $header => $headerValue) {
                $publicHeaders[] = new Header($header, $headerValue, null);
            }
        }
        $headersMap = array_merge($unprotectedHeadersMap ?? [], $protectedHeadersMap);
        if (array_key_exists('cty', $headersMap)) {
            if ($headersMap['cty'] === NestedPayloadInterface::CONTENT_TYPE) {
                $payload = new NestedPayload($payload);
            } else {
                $payload = new ArbitraryPayload($payload);
            }
        } else {
            $claimData = json_decode($payload, true);
            $claims = [];
            foreach ($claimData as $name => $value) {
                $claims[] = new Claim($name, $value, null);
            }
            $payload = new ClaimsPayload($claims);
        }

        return new Jws(
            [new JwsHeader($protectedHeaders)],
            $payload,
            $publicHeaders ? [new JwsHeader($publicHeaders)] : null
        );
    }
}
