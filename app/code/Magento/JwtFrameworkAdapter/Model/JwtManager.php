<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifierFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManagerFactory;
use Jose\Easy\AlgorithmProvider;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkSet;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\Jws\JwsSignatureSettingsInterface;
use Magento\Framework\Jwt\JwtInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Payload\ArbitraryPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Jwt\Payload\NestedPayload;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;
use Jose\Component\Core\JWK as AdapterJwk;
use Jose\Component\Signature\Serializer\CompactSerializer as JwsCompactSerializer;
use Jose\Component\Signature\Serializer\JSONGeneralSerializer as JwsJsonSerializer;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer as JwsFlatSerializer;
use Jose\Component\Core\JWKSet as AdapterJwkSet;
use Jose\Component\Signature\JWSLoaderFactory;
use Magento\JwtFrameworkAdapter\Model\Data\Claim;
use Magento\JwtFrameworkAdapter\Model\Data\Header;

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
     * @var JWSLoaderFactory
     */
    private $jwsLoaderFactory;

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
        $jwsAlgorithmProvider = new AlgorithmProvider($jwsAlgorithms);
        $algorithmManager = new AlgorithmManager($jwsAlgorithmProvider->getAvailableAlgorithms());
        $this->jwsBuilder = new JWSBuilder($algorithmManager);
        $this->jwsCompactSerializer = new JwsCompactSerializer();
        $this->jwsJsonSerializer = new JwsJsonSerializer();
        $this->jwsFlatSerializer = new JwsFlatSerializer();
        $jwsSerializerFactory = new JWSSerializerManagerFactory();
        $jwsSerializerFactory->add(new CompactSerializer());
        $jwsSerializerFactory->add(new JwsJsonSerializer());
        $jwsSerializerFactory->add(new JwsFlatSerializer());
        $jwsAlgorithmFactory = new AlgorithmManagerFactory();
        foreach ($jwsAlgorithmProvider->getAvailableAlgorithms() as $algorithm) {
            $jwsAlgorithmFactory->add($algorithm->name(), $algorithm);
        }
        $this->jwsLoaderFactory = new JWSLoaderFactory(
            $jwsSerializerFactory,
            new JWSVerifierFactory($jwsAlgorithmFactory),
            null
        );
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
        /** @var JwtInterface|null $read */
        $read = null;
        /** @var \Throwable|null $lastException */
        $lastException = null;
        foreach ($acceptableEncryption as $encryptionSettings) {
            switch ($this->detectJwtType($encryptionSettings)) {
                case self::JWT_TYPE_JWS:
                    try {
                        $read = $this->readJws($token, $encryptionSettings);
                    } catch (\Throwable $exception) {
                        $lastException = $exception;
                    }
                    break;
            }
        }

        if (!$read) {
            throw new JwtException('Failed to read JWT', 0, $lastException);
        }
        return $read;
    }

    /**
     * Convert JWK.
     *
     * @param Jwk $jwk
     * @return AdapterJwk
     */
    private function convertToAdapterJwk(Jwk $jwk): AdapterJwk
    {
        return new AdapterJwk($jwk->getJsonData());
    }

    private function convertToAdapterKeySet(JwkSet $jwkSet): AdapterJwkSet
    {
        return new AdapterJwkSet(array_map([$this, 'convertToAdapterJwk'], $jwkSet->getKeys()));
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
            for ($i = 0; $i < $signaturesCount; $i++) {
                $jwk = $encryptionSettings->getJwkSet()->getKeys()[$i];
                $alg = $jwk->getAlgorithm();
                if (!$alg) {
                    throw new EncryptionException('Algorithm is required for JWKs');
                }
                $protected = [];
                if ($jws->getPayload()->getContentType()) {
                    $protected['cty'] = $jws->getPayload()->getContentType();
                }
                if ($jws->getProtectedHeaders()) {
                    $protected = $this->extractHeaderData($jws->getProtectedHeaders()[$i]);
                }
                $protected['alg'] = $alg;
                $unprotected = [];
                if ($jws->getUnprotectedHeaders()) {
                    $unprotected = $this->extractHeaderData($jws->getUnprotectedHeaders()[$i]);
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

    /**
     * Read and verify a JWS token.
     *
     * @param string $token
     * @param EncryptionSettingsInterface|JwsSignatureJwks $encryptionSettings
     * @return JwtInterface
     */
    private function readJws(string $token, EncryptionSettingsInterface $encryptionSettings): JwtInterface
    {
        if (!$encryptionSettings instanceof JwsSignatureJwks) {
            throw new JwtException('Can only work with JWK settings for JWS tokens');
        }

        $loader = $this->jwsLoaderFactory->create(
            ['jws_compact', 'jws_json_flattened', 'jws_json_general'],
            array_map(
                function (Jwk $jwk) {
                    return $jwk->getAlgorithm();
                },
                $encryptionSettings->getJwkSet()->getKeys()
            )
        );
        $jws = $loader->loadAndVerifyWithKeySet(
            $token, $this->convertToAdapterKeySet($encryptionSettings->getJwkSet()),
            $signature,
            null
        );
        if ($signature === null) {
            throw new EncryptionException('Failed to verify a JWS token');
        }
        $headers = $jws->getSignature($signature);
        $protectedHeaders = [];
        foreach ($headers->getProtectedHeader() as $header => $headerValue) {
            $protectedHeaders[] = new Header($header, $headerValue, null);
        }
        $publicHeaders = null;
        if ($headers->getHeader()) {
            $publicHeaders = [];
            foreach ($headers->getHeader() as $header => $headerValue) {
                $publicHeaders[] = new Header($header, $headerValue, null);
            }
        }
        if ($jws->isPayloadDetached()) {
            throw new JwtException('Detached payload is not supported');
        }
        $headersMap = array_merge($headers->getHeader(), $headers->getProtectedHeader());
        if (array_key_exists('cty', $headersMap)) {
            if ($headersMap['cty'] === NestedPayloadInterface::CONTENT_TYPE) {
                $payload = new NestedPayload($jws->getPayload());
            } else {
                $payload = new ArbitraryPayload($jws->getPayload());
            }
        } else {
            $claimData = json_decode($jws->getPayload(), true);
            $claims = [];
            foreach ($claimData as $name => $value) {
                $claims[] = new Claim($name, $value, null);
            }
            $payload = new ClaimsPayload($claims);
        }

        return new Jws([new JwsHeader($protectedHeaders)], $payload, $publicHeaders ? [new JwsHeader($publicHeaders)] : null);
    }
}
