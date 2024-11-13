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
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwk;
use Jose\Component\Core\JWK as AdapterJwk;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;

/**
 * Works with Unsecured JWT.
 */
class UnsecuredJwtManager
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
     * @var UnsecuredJwtFactory
     */
    private $jwtFactory;

    /**
     * @param JwsBuilderFactory $builderFactory
     * @param JwsSerializerPoolFactory $serializerPoolFactory
     * @param JwsLoaderFactory $jwsLoaderFactory
     * @param UnsecuredJwtFactory $jwtFactory
     */
    public function __construct(
        JwsBuilderFactory $builderFactory,
        JwsSerializerPoolFactory $serializerPoolFactory,
        JwsLoaderFactory $jwsLoaderFactory,
        UnsecuredJwtFactory $jwtFactory
    ) {
        $this->jwsBuilder = $builderFactory->create();
        $this->jwsSerializer = $serializerPoolFactory->create();
        $this->jwsLoader = $jwsLoaderFactory->create();
        $this->jwtFactory = $jwtFactory;
    }

    /**
     * Generate unsecured JWT token.
     *
     * @param UnsecuredJwtInterface $jwt
     * @return string
     * @throws JwtException
     */
    public function build(UnsecuredJwtInterface $jwt): string
    {
        $signaturesCount = count($jwt->getProtectedHeaders());
        if ($jwt->getUnprotectedHeaders()
            && count($jwt->getUnprotectedHeaders()) !== $signaturesCount
        ) {
            throw new MalformedTokenException('There must be an equal number of protected and unprotected headers.');
        }
        $builder = $this->jwsBuilder->create();
        $builder = $builder->withPayload($jwt->getPayload()->getContent());
        for ($i = 0; $i < $signaturesCount; $i++) {
            $protected = [];
            if ($jwt->getPayload()->getContentType()) {
                $protected['cty'] = $jwt->getPayload()->getContentType();
            }
            if ($jwt->getProtectedHeaders()) {
                $protected = $this->extractHeaderData($jwt->getProtectedHeaders()[$i]);
            }
            $protected['alg'] = Jwk::ALGORITHM_NONE;
            $unprotected = [];
            if ($jwt->getUnprotectedHeaders()) {
                $unprotected = $this->extractHeaderData($jwt->getUnprotectedHeaders()[$i]);
            }
            $builder = $builder->addSignature(
                new AdapterJwk(['kty' => 'none', 'alg' => 'none']),
                $protected,
                $unprotected
            );
        }
        $jwsCreated = $builder->build();

        if ($signaturesCount > 1) {
            return $this->jwsSerializer->serialize('jws_json_general', $jwsCreated);
        }
        if ($jwt->getUnprotectedHeaders()) {
            return $this->jwsSerializer->serialize('jws_json_flattened', $jwsCreated);
        }
        return $this->jwsSerializer->serialize('jws_compact', $jwsCreated);
    }

    /**
     * Read unsecured JWT token.
     *
     * @param string $token
     * @return UnsecuredJwtInterface
     * @throws JwtException
     */
    public function read(string $token): UnsecuredJwtInterface
    {
        try {
            $jws = $this->jwsLoader->loadAndVerifyWithKey(
                $token,
                new AdapterJwk(['kty' => 'none', 'alg' => 'none']),
                $signature,
                null
            );
        } catch (\Throwable $exception) {
            throw new MalformedTokenException('Failed to read JWT token', 0, $exception);
        }
        if ($jws->isPayloadDetached()) {
            throw new JwtException('Detached payload is not supported');
        }
        $protectedHeaders = [];
        $publicHeaders = [];
        foreach ($jws->getSignatures() as $signature) {
            $protectedHeaders[] = $signature->getProtectedHeader();
            if ($signature->getHeader()) {
                $publicHeaders[] = $signature->getHeader();
            }
        }

        return $this->jwtFactory->create(
            $protectedHeaders,
            $publicHeaders,
            $jws->getPayload()
        );
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
