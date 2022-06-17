<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Jose\Component\Core\JWK as AdapterJwk;
use Jose\Component\Core\JWKSet as AdapterJwkSet;
use Magento\JwtFrameworkAdapter\Model\Data\Header;

/**
 * Works with JWS.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JwsManager
{
    /**
     * @var JWSBuilder
     */
    private $jwsBuilder;

    /**
     * @var JWSLoader
     */
    private $jwsLoader;

    /**
     * @var JWSSerializerManager
     */
    private $jwsSerializer;

    /**
     * @var JwsFactory
     */
    private $jwsFactory;

    /**
     * @param JwsBuilderFactory $builderFactory
     * @param JwsSerializerPoolFactory $serializerPoolFactory
     * @param JwsLoaderFactory $jwsLoaderFactory
     * @param JwsFactory $jwsFactory
     */
    public function __construct(
        JwsBuilderFactory $builderFactory,
        JwsSerializerPoolFactory $serializerPoolFactory,
        JwsLoaderFactory $jwsLoaderFactory,
        JwsFactory $jwsFactory
    ) {
        $this->jwsBuilder = $builderFactory->create();
        $this->jwsSerializer = $serializerPoolFactory->create();
        $this->jwsLoader = $jwsLoaderFactory->create();
        $this->jwsFactory = $jwsFactory;
    }

    /**
     * Generate JWS token.
     *
     * @param JwsInterface $jws
     * @param EncryptionSettingsInterface|JwsSignatureJwks $encryptionSettings
     * @return string
     * @throws JwtException
     */
    public function build(JwsInterface $jws, EncryptionSettingsInterface $encryptionSettings): string
    {
        $this->validate($jws, $encryptionSettings);
        $builder = $this->jwsBuilder->create();
        $builder = $builder->withPayload($jws->getPayload()->getContent());
        $signaturesCount = count($encryptionSettings->getJwkSet()->getKeys());

        for ($i = 0; $i < $signaturesCount; $i++) {
            $jwk = $encryptionSettings->getJwkSet()->getKeys()[$i];
            $protected = [];
            if ($jws->getPayload()->getContentType()) {
                $protected['cty'] = $jws->getPayload()->getContentType();
            }
            if ($jwk->getKeyId()) {
                $protected['kid'] = $jwk->getKeyId();
            }
            if ($jws->getProtectedHeaders()) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $protected = array_merge($protected, $this->extractHeaderData($jws->getProtectedHeaders()[$i]));
            }
            $protected['alg'] = $protected['alg'] ?? $jwk->getAlgorithm();
            if (!$protected['alg']) {
                throw new EncryptionException('Algorithm is required for JWKs');
            }
            $unprotected = [];
            if ($jws->getUnprotectedHeaders()) {
                $unprotected = $this->extractHeaderData($jws->getUnprotectedHeaders()[$i]);
            }
            $builder = $builder->addSignature(new AdapterJwk($jwk->getJsonData()), $protected, $unprotected);
        }
        $jwsCreated = $builder->build();

        if ($signaturesCount > 1) {
            return $this->jwsSerializer->serialize('jws_json_general', $jwsCreated);
        }
        if ($jws->getUnprotectedHeaders()) {
            return $this->jwsSerializer->serialize('jws_json_flattened', $jwsCreated);
        }
        return $this->jwsSerializer->serialize('jws_compact', $jwsCreated);
    }

    /**
     * Validate jws and encryption settings.
     *
     * @param JwsInterface $jws
     * @param EncryptionSettingsInterface $encryptionSettings
     */
    private function validate(JwsInterface $jws, EncryptionSettingsInterface $encryptionSettings): void
    {
        if (!$encryptionSettings instanceof JwsSignatureJwks) {
            throw new JwtException('Can only work with JWK encryption settings for JWS tokens');
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
    }

    /**
     * Read and verify JWS token.
     *
     * @param string $token
     * @param EncryptionSettingsInterface|JwsSignatureJwks $encryptionSettings
     * @return JwsInterface
     * @throws JwtException
     */
    public function read(string $token, EncryptionSettingsInterface $encryptionSettings): JwsInterface
    {
        if (!$encryptionSettings instanceof JwsSignatureJwks) {
            throw new JwtException('Can only work with JWK settings for JWS tokens');
        }

        $jwkSet = new AdapterJwkSet(
            array_map(
                function (Jwk $jwk) {
                    return new AdapterJwk($jwk->getJsonData());
                },
                $encryptionSettings->getJwkSet()->getKeys()
            )
        );
        try {
            $jws = $this->jwsLoader->loadAndVerifyWithKeySet(
                $token,
                $jwkSet,
                $signature,
                null
            );
        } catch (\Throwable $exception) {
            throw new MalformedTokenException('Failed to read JWS token', 0, $exception);
        }
        if ($signature === null) {
            throw new EncryptionException('Failed to verify a JWS token');
        }
        $headers = $jws->getSignature($signature);
        if ($jws->isPayloadDetached()) {
            throw new JwtException('Detached payload is not supported');
        }

        return $this->jwsFactory->create(
            $headers->getProtectedHeader(),
            $jws->getPayload(),
            $headers->getHeader() ? $headers->getHeader() : null
        );
    }

    /**
     * Read JWS headers.
     *
     * @param string $token
     * @return HeaderInterface[]
     */
    public function readHeaders(string $token): array
    {
        try {
            $jws = $this->jwsSerializer->unserialize($token);
        } catch (\Throwable $exception) {
            throw new JwtException('Failed to read JWS headers');
        }
        $headers = [];
        $headersValues = [];
        foreach ($jws->getSignatures() as $signature) {
            if ($signature->getProtectedHeader()) {
                $headersValues[] = $signature->getProtectedHeader();
            }
            if ($signature->getHeader()) {
                $headersValues[] = $signature->getHeader();
            }
        }
        foreach ($headersValues as $headerValues) {
            $params = [];
            foreach ($headerValues as $header => $value) {
                $params[] = new Header($header, $value, null);
            }
            if ($params) {
                $headers[] = new JwsHeader($params);
            }
        }

        return $headers;
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
}
