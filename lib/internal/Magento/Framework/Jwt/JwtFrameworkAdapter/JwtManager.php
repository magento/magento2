<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\JwtFrameworkAdapter;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;
use Jose\Easy\AlgorithmProvider;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\JwtInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;
use Jose\Component\Core\JWK as AdapterJwk;
use Jose\Component\Signature\Serializer\CompactSerializer as JwsCompactSerializer;
use Jose\Component\Signature\Serializer\JSONGeneralSerializer as JwsJsonSerializer;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer as JwsFlatSerializer;

/**
 * Adapter for jwt-framework.
 */
class JwtManager implements JwtManagerInterface
{
    /**
     * @var JWSBuilder
     */
    private $jwsBuilder;

    /**
     * @var JwsCompactSerializer
     */
    private $jwsCompactSerializer;

    /**
     * @var JwsJsonSerializer
     */
    private $jwsJsonSerializer;

    /**
     * @var JwsFlatSerializer
     */
    private $jwsFlatSerializer;

    /**
     * JwtManager constructor.
     */
    public function __construct()
    {
        $jwsAlgorithms = [
            \Jose\Component\Signature\Algorithm\HS256::class,
            \Jose\Component\Signature\Algorithm\HS384::class,
            \Jose\Component\Signature\Algorithm\HS512::class,
            \Jose\Component\Signature\Algorithm\RS256::class,
            \Jose\Component\Signature\Algorithm\RS384::class,
            \Jose\Component\Signature\Algorithm\RS512::class,
            \Jose\Component\Signature\Algorithm\PS256::class,
            \Jose\Component\Signature\Algorithm\PS384::class,
            \Jose\Component\Signature\Algorithm\PS512::class,
            \Jose\Component\Signature\Algorithm\ES256::class,
            \Jose\Component\Signature\Algorithm\ES384::class,
            \Jose\Component\Signature\Algorithm\ES512::class,
            \Jose\Component\Signature\Algorithm\EdDSA::class,
        ];
        $this->jwsBuilder = new JWSBuilder(
            new AlgorithmManager((new AlgorithmProvider($jwsAlgorithms))->getAvailableAlgorithms())
        );
        $this->jwsCompactSerializer = new JwsCompactSerializer();
        $this->jwsJsonSerializer = new JwsJsonSerializer();
        $this->jwsFlatSerializer = new JwsFlatSerializer();
    }

    /**
     * @inheritDoc
     */
    public function create(JwtInterface $jwt, EncryptionSettingsInterface $encryption): string
    {
        if (!$jwt instanceof UnsecuredJwtInterface && !$jwt instanceof JwsInterface && !$jwt instanceof JweInterface) {
            throw new MalformedTokenException('Can only build JWS, JWE or Unsecured tokens.');
        }
        if ($jwt instanceof JwsInterface) {
            return $this->buildJws($jwt, $encryption);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(string $token, array $acceptableEncryption): JwtInterface
    {
        // TODO: Implement read() method.
    }

    /**
     * Convert JWK.
     *
     * @param Jwk $jwk
     * @return AdapterJwk
     */
    private function convertToAdapterJwk(Jwk $jwk): AdapterJwk
    {
        $data = [
            'kty' => $jwk->getKeyType(),
            'use' => $jwk->getPublicKeyUse(),
            'key_ops' => $jwk->getKeyOperations(),
            'alg' => $jwk->getAlgorithm(),
            'x5u' => $jwk->getX509Url(),
            'x5c' => $jwk->getX509CertificateChain(),
            'x5t' => $jwk->getX509Sha1Thumbprint(),
            'x5t#S256' => $jwk->getX509Sha256Thumbprint()
        ];

        return new AdapterJwk(array_merge($data, $jwk->getAlgoData()));
    }

    /**
     * Extract JOSE header data.
     *
     * @param HeaderInterface $header
     * @return array
     */
    private function extractHeaderData(HeaderInterface $header): array
    {
        $data = [];
        foreach ($header->getParameters() as $parameter) {
            $data[$parameter->getName()] = $parameter->getValue();
        }

        return $data;
    }

    /**
     * Create a JWS.
     *
     * @param JwsInterface $jws
     * @param EncryptionSettingsInterface|JwsSignatureJwks $encryptionSettings
     * @return string
     * @throws JwtException
     */
    private function buildJws(JwsInterface $jws, EncryptionSettingsInterface $encryptionSettings): string
    {
        if (!$encryptionSettings instanceof JwsSignatureJwks) {
            throw new JwtException('Can only work with JWK settings for JWS tokens');
        }
        $signaturesCount = count($encryptionSettings->getJwkSet()->getKeys());
        if ($jws->getProtectedHeaders() && count($jws->getProtectedHeaders()) !== $signaturesCount) {
            throw new MalformedTokenException('Number of headers must equal to number of JWKs');
        }
        if ($jws->getUnprotectedHeaders()
            && count($jws->getUnprotectedHeaders()) !== $signaturesCount
        ) {
            throw new MalformedTokenException('There must be an equal number of protected and unprotected headers.');
        }

        try {
            $builder = $this->jwsBuilder->create();
            $builder = $builder->withPayload($jws->getPayload()->getContent());
            for ($i = 0; $i <= $signaturesCount; $i++) {
                $jwk = $encryptionSettings->getJwkSet()->getKeys()[$i];
                $alg = $jwk->getAlgorithm();
                if (!$alg) {
                    throw new EncryptionException('Algorithm is required for JWKs');
                }
                $protected = [];
                if ($jws->getProtectedHeaders()) {
                    $protected = $this->extractHeaderData($jws->getProtectedHeaders()[$i]);
                }
                $protected['alg'] = $alg;
                $unprotected = [];
                if ($jws->getUnprotectedHeaders()) {
                    $unprotected = $this->extractHeaderData($jws->getUnprotectedHeaders()[$i]);
                    $unprotected['alg'] = $alg;
                }
                $builder = $builder->addSignature($this->convertToAdapterJwk($jwk), $protected, $unprotected);
            }
            $jwsCreated = $builder->build();

            if ($signaturesCount > 1) {
                return $this->jwsJsonSerializer->serialize($jwsCreated);
            }
            if ($jws->getUnprotectedHeaders()) {
                return $this->jwsFlatSerializer->serialize($jwsCreated);
            }
            return $this->jwsCompactSerializer->serialize($jwsCreated);
        } catch (\Throwable $exception) {
            if (!$exception instanceof JwtException) {
                $exception = new JwtException('Something went wrong while generating a JWS', 0, $exception);
            }
            throw $exception;
        }
    }
}
