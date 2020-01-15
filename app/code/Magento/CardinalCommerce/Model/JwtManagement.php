<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Encryption\Helper\Security;

/**
 * JSON Web Token management.
 */
class JwtManagement
{
    /**
     * The signing algorithm. Cardinal supported algorithm is 'HS256'
     */
    private const SIGN_ALGORITHM = 'HS256';

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Converts JWT string into array.
     *
     * @param string $jwt The JWT
     * @param string $key The secret key
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $jwt, string $key): array
    {
        if (empty($jwt)) {
            throw new \InvalidArgumentException('JWT is empty');
        }

        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new \InvalidArgumentException('Wrong number of segments in JWT');
        }

        [$headB64, $payloadB64, $signatureB64] = $parts;

        $headerJson = $this->urlSafeB64Decode($headB64);
        $header = $this->json->unserialize($headerJson);

        $payloadJson  = $this->urlSafeB64Decode($payloadB64);
        $payload = $this->json->unserialize($payloadJson);

        $signature = $this->urlSafeB64Decode($signatureB64);

        if (!Security::compareStrings($signature, $this->sign($headB64 . '.' . $payloadB64, $key, $header['alg']))) {
            throw new \InvalidArgumentException('JWT signature verification failed');
        }

        return $payload;
    }

    /**
     * Converts and signs array into a JWT string.
     *
     * @param array $payload
     * @param string $key
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function encode(array $payload, string $key): string
    {
        $header = ['typ' => 'JWT', 'alg' => self::SIGN_ALGORITHM];

        $headerJson = $this->json->serialize($header);
        $segments[] = $this->urlSafeB64Encode($headerJson);

        $payloadJson = $this->json->serialize($payload);
        $segments[] = $this->urlSafeB64Encode($payloadJson);

        $signature = $this->sign(implode('.', $segments), $key, $header['alg']);
        $segments[] = $this->urlSafeB64Encode($signature);

        return implode('.', $segments);
    }
    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg The message to sign.
     * @param string $key The secret key.
     * @param string $algorithm The signing algorithm.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function sign(string $msg, string $key, string $algorithm): string
    {
        if ($algorithm !== self::SIGN_ALGORITHM) {
            throw new \InvalidArgumentException('Algorithm ' . $algorithm . ' is not supported');
        }

        return hash_hmac('sha256', $msg, $key, true);
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string
     */
    private function urlSafeB64Decode(string $input): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode(
            str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT)
        );
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string
     */
    private function urlSafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}
