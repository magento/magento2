<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwe\JweEncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureSettingsInterface;
use Magento\Framework\Jwt\JwtInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Unsecured\NoEncryption;
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

    private const JWE_ALGORITHMS = [
        Jwk::ALGORITHM_RSA_OAEP,
        Jwk::ALGORITHM_RSA_OAEP_256,
        Jwk::ALGORITHM_A128KW,
        Jwk::ALGORITHM_A192KW,
        Jwk::ALGORITHM_A256KW,
        Jwk::ALGORITHM_DIR,
        Jwk::ALGORITHM_ECDH_ES,
        Jwk::ALGORITHM_ECDH_ES_A128KW,
        Jwk::ALGORITHM_ECDH_ES_A192KW,
        Jwk::ALGORITHM_ECDH_ES_A256KW,
        Jwk::ALGORITHM_A128GCMKW,
        Jwk::ALGORITHM_A192GCMKW,
        Jwk::ALGORITHM_A256GCMKW,
        Jwk::ALGORITHM_PBES2_HS256_A128KW,
        Jwk::ALGORITHM_PBES2_HS384_A192KW,
        Jwk::ALGORITHM_PBES2_HS512_A256KW,
    ];

    /**
     * @var JwsManager
     */
    private $jwsManager;

    /**
     * @var JweManager
     */
    private $jweManager;

    /**
     * @var UnsecuredJwtManager
     */
    private $unsecuredManager;

    /**
     * @param JwsManager $jwsManager
     * @param JweManager $jweManager
     */
    public function __construct(JwsManager $jwsManager, JweManager $jweManager, UnsecuredJwtManager $unsecuredManager)
    {
        $this->jwsManager = $jwsManager;
        $this->jweManager = $jweManager;
        $this->unsecuredManager = $unsecuredManager;
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
            if ($jwt instanceof JweInterface) {
                return $this->jweManager->build($jwt, $encryption);
            }
            if ($jwt instanceof UnsecuredJwtInterface) {
                if (!$encryption instanceof NoEncryption) {
                    throw new EncryptionException('Unsecured JWTs can only work with no encryption settings');
                }

                return $this->unsecuredManager->build($jwt);
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
            try {
                switch ($this->detectJwtType($encryptionSettings)) {
                    case self::JWT_TYPE_JWS:
                        $read = $this->jwsManager->read($token, $encryptionSettings);
                        break;
                    case self::JWT_TYPE_JWE:
                        $read = $this->jweManager->read($token, $encryptionSettings);
                        break;
                    case self::JWT_TYPE_UNSECURED:
                        $read = $this->unsecuredManager->read($token);
                        break;
                }
            } catch (\Throwable $exception) {
                if (!$exception instanceof JwtException) {
                    $exception = new JwtException('Failed to read JWT', 0, $exception);
                }
                $lastException = $exception;
            }
        }

        if (!$read) {
            throw $lastException;
        }
        return $read;
    }

    /**
     * @inheritDoc
     */
    public function readHeaders(string $token): array
    {
        try {
            return $this->jwsManager->readHeaders($token);
        } catch (JwtException $exception) {
            return $this->jweManager->readHeaders($token);
        }
    }

    private function detectJwtType(EncryptionSettingsInterface $encryptionSettings): int
    {
        if ($encryptionSettings instanceof JwsSignatureSettingsInterface) {
            return self::JWT_TYPE_JWS;
        }
        if ($encryptionSettings instanceof JweEncryptionSettingsInterface) {
            return self::JWT_TYPE_JWE;
        }
        if ($encryptionSettings instanceof NoEncryption) {
            return self::JWT_TYPE_UNSECURED;
        }

        if ($encryptionSettings->getAlgorithmName() === Jwk::ALGORITHM_NONE) {
            return self::JWT_TYPE_UNSECURED;
        }
        if (in_array($encryptionSettings->getAlgorithmName(), self::JWS_ALGORITHMS, true)) {
            return self::JWT_TYPE_JWS;
        }
        if (in_array($encryptionSettings->getAlgorithmName(), self::JWE_ALGORITHMS, true)) {
            return self::JWT_TYPE_JWE;
        }

        throw new \RuntimeException('Failed to determine JWT type');
    }
}
