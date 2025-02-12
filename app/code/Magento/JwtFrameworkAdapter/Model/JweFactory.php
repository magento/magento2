<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Magento\Framework\Jwt\Jwe\Jwe;
use Magento\Framework\Jwt\Jwe\JweHeader;
use Magento\Framework\Jwt\Jwe\JweInterface;
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
 * Create JWE data object.
 */
class JweFactory
{
    public function create(
        array $protectedHeadersMap,
        string $payload,
        ?array $unprotectedHeadersMap,
        ?array $recipientHeadersMap
    ): JweInterface {
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
        $recipientHeader = null;
        if ($recipientHeadersMap) {
            $recipientHeader = [];
            foreach ($recipientHeadersMap as $header => $headerValue) {
                $recipientHeader[] = new Header($header, $headerValue, null);
            }
        }
        $headersMap = array_merge($unprotectedHeadersMap ?? [], $recipientHeader ?? [], $protectedHeadersMap);
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

        return new Jwe(
            new JweHeader($protectedHeaders),
            $publicHeaders ? new JweHeader($publicHeaders) : null,
            $recipientHeader ? [new JweHeader($recipientHeader)] : null,
            $payload
        );
    }
}
