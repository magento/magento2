<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Payload\ArbitraryPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Jwt\Payload\NestedPayload;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwt;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;
use Magento\JwtFrameworkAdapter\Model\Data\Claim;
use Magento\JwtFrameworkAdapter\Model\Data\Header;

/**
 * Creates unsecure JWT DTOs.
 */
class UnsecuredJwtFactory
{
    public function create(
        array $protectedHeaderMaps,
        ?array $unprotectedHeaderMaps,
        string $payload
    ): UnsecuredJwtInterface {
        $cty = null;
        $protectedHeaders = [];
        foreach ($protectedHeaderMaps as $protectedHeaderMap) {
            $parameters = [];
            foreach ($protectedHeaderMap as $header => $headerValue) {
                $parameters[] = new Header($header, $headerValue, null);
                if ($header === 'cty') {
                    $cty = $headerValue;
                }
            }
            $protectedHeaders[] = new JwsHeader($parameters);
        }
        $publicHeaders = null;
        if ($unprotectedHeaderMaps) {
            $publicHeaders = [];
            foreach ($unprotectedHeaderMaps as $unprotectedHeaderMap) {
                $parameters = [];
                foreach ($unprotectedHeaderMap as $header => $headerValue) {
                    $parameters[] = new Header($header, $headerValue, null);
                    if ($header === 'cty') {
                        $cty = $headerValue;
                    }
                }
                $publicHeaders[] = new JwsHeader($parameters);
            }
        }
        if ($cty) {
            if ($cty === NestedPayloadInterface::CONTENT_TYPE) {
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

        return new UnsecuredJwt(
            $protectedHeaders,
            $payload,
            $publicHeaders
        );
    }
}
