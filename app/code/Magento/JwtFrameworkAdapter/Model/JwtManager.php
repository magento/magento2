<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureSettingsInterface;
use Magento\Framework\Jwt\JwtInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;

/**
 * Adapter for jwt-framework.
 */
class JwtManager implements JwtManagerInterface
{
    private const JWT_TYPE_JWS = 1;

    private const JWT_TYPE_JWE = 2;

    private const JWT_TYPE_UNSECURED = 3;

    private const JWS_ALGORITHMS = [
        Jwk::ALGORITHM_HS256,
        Jwk::ALGORITHM_HS384,
        Jwk::ALGORITHM_HS512,
        Jwk::ALGORITHM_RS256,
        Jwk::ALGORITHM_RS384,
        Jwk::ALGORITHM_RS512,
        Jwk::ALGORITHM_ES256,
        Jwk::ALGORITHM_ES384,
        Jwk::ALGORITHM_ES512,
        Jwk::ALGORITHM_PS256,
        Jwk::ALGORITHM_PS384,
        Jwk::ALGORITHM_PS512
    ];

    /**
     * @var JwsManager
     */
    private $jwsManager;

    /**
     * @param JwsManager $jwsManager
     */
    public function __construct(JwsManager $jwsManager)
    {
        $this->jwsManager = $jwsManager;
    }

    /**
     * @inheritDoc
     */
    public function create(JwtInterface $jwt, EncryptionSettingsInterface $encryption): string
    {
        if (!$jwt instanceof UnsecuredJwtInterface && !$jwt instanceof JwsInterface && !$jwt instanceof JweInterface) {
            throw new MalformedTokenException('Can only build JWS, JWE or Unsecured tokens.');
        }
        try {
            if ($jwt instanceof JwsInterface) {
                return $this->jwsManager->build($jwt, $encryption);
            }
        } catch (\Throwable $exception) {
            if (!$exception instanceof JwtException) {
                $exception = new JwtException('Failed to generate a JWT', 0, $exception);
            }
            throw $exception;
        }
    }

    /**
     * @inheritDoc
     */
    public function read(string $token, array $acceptableEncryption): JwtInterface
    {
        /** @var JwtInterface|null $read */
        $read = null;
        /** @var \Throwable|null $lastException */
        $lastException = null;
        foreach ($acceptableEncryption as $encryptionSettings) {
            switch ($this->detectJwtType($encryptionSettings)) {
                case self::JWT_TYPE_JWS:
                    try {
                        $read = $this->jwsManager->read($token, $encryptionSettings);
                    } catch (\Throwable $exception) {
                        if (!$exception instanceof JwtException) {
                            $exception = new JwtException('Failed to read JWT', 0, $exception);
                        }
                        $lastException = $exception;
                    }
                    break;
            }
        }

        if (!$read) {
            throw $lastException;
        }
        return $read;
    }

    private function detectJwtType(EncryptionSettingsInterface $encryptionSettings): int
    {
        if ($encryptionSettings instanceof JwsSignatureSettingsInterface) {
            return self::JWT_TYPE_JWS;
        }

        if ($encryptionSettings->getAlgorithmName() === Jwk::ALGORITHM_NONE) {
            return self::JWT_TYPE_UNSECURED;
        }
        if (in_array($encryptionSettings->getAlgorithmName(), self::JWS_ALGORITHMS, true)) {
            return self::JWT_TYPE_JWS;
        }

        throw new \RuntimeException('Failed to determine JWT type');
    }
}
