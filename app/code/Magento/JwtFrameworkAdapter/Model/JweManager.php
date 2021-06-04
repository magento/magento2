<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Exception\MalformedTokenException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Jwe\JweEncryptionJwks;
use Magento\Framework\Jwt\Jwe\JweHeader;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Jose\Component\Core\JWK as AdapterJwk;
use Jose\Component\Core\JWKSet as AdapterJwkSet;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\JwtFrameworkAdapter\Model\Data\Header;

/**
 * Works with JWE
 */
class JweManager
{
    /**
     * @var JWEBuilder
     */
    private $builder;

    /**
     * @var JWESerializerManager
     */
    private $serializer;

    /**
     * @var JWELoader
     */
    private $loader;

    /**
     * @var JweFactory
     */
    private $jweFactory;

    public function __construct(
        JweBuilderFactory $jweBuilderFactory,
        JweSerializerPoolFactory $serializerPoolFactory,
        JweLoaderFactory $jweLoaderFactory,
        JweFactory $jweFactory
    ) {
        $this->builder = $jweBuilderFactory->create();
        $this->serializer = $serializerPoolFactory->create();
        $this->loader = $jweLoaderFactory->create();
        $this->jweFactory = $jweFactory;
    }

    /**
     * Generate JWE token.
     *
     * @param JweInterface $jwe
     * @param EncryptionSettingsInterface|JweEncryptionJwks $encryptionSettings
     * @return string
     */
    public function build(JweInterface $jwe, EncryptionSettingsInterface $encryptionSettings): string
    {
        $this->validateJweSettings($jwe, $encryptionSettings);

        $builder = $this->builder->create();

        $payload = $jwe->getPayload();
        $builder = $builder->withPayload($payload->getContent());

        $sharedProtected = $this->extractHeaderData($jwe->getProtectedHeader());
        $sharedProtected['enc'] = $encryptionSettings->getContentEncryptionAlgorithm();
        if ($payload->getContentType()) {
            $sharedProtected['cty'] = $payload->getContentType();
        }
        if (!$jwe->getPerRecipientUnprotectedHeaders()) {
            $sharedProtected['alg'] = $encryptionSettings->getAlgorithmName();
        }
        if ($payload instanceof ClaimsPayloadInterface) {
            foreach ($payload->getClaims() as $claim) {
                if ($claim->isHeaderDuplicated()) {
                    $sharedProtected[$claim->getName()] = $claim->getValue();
                }
            }
        }
        $builder = $builder->withSharedProtectedHeader($sharedProtected);

        $sharedUnprotected = [];
        if ($jwe->getSharedUnprotectedHeader()) {
            $sharedUnprotected = array_merge(
                $this->extractHeaderData($jwe->getSharedUnprotectedHeader()),
                $sharedUnprotected
            );
        }
        if ($sharedUnprotected) {
            $builder = $builder->withSharedHeader($sharedUnprotected);
        }

        if (!$jwe->getPerRecipientUnprotectedHeaders()) {
            $builder = $builder->addRecipient(
                new AdapterJwk($encryptionSettings->getJwkSet()->getKeys()[0]->getJsonData())
            );
        } else {
            foreach ($jwe->getPerRecipientUnprotectedHeaders() as $i => $header) {
                $jwk = $encryptionSettings->getJwkSet()->getKeys()[$i];
                $headerData = [];
                if ($jwk->getKeyId()) {
                    $headerData['kid'] = $jwk->getKeyId();
                }
                $headerData = array_merge($headerData, $this->extractHeaderData($header));
                $headerData['alg'] = $jwk->getAlgorithm();
                $builder = $builder->addRecipient(new AdapterJwk($jwk->getJsonData()), $headerData);
            }
        }

        $built = $builder->build();
        if ($jwe->getPerRecipientUnprotectedHeaders()
            && count($jwe->getPerRecipientUnprotectedHeaders()) === 1
            || (!$jwe->getPerRecipientUnprotectedHeaders() && $jwe->getSharedUnprotectedHeader())
        ) {
            return $this->serializer->serialize('jwe_json_flattened', $built);
        }
        if ($jwe->getPerRecipientUnprotectedHeaders()) {
            return $this->serializer->serialize('jwe_json_general', $built);
        }
        return $this->serializer->serialize('jwe_compact', $built);
    }

    /**
     * Read JWE token.
     *
     * @param string $token
     * @param EncryptionSettingsInterface|JweEncryptionJwks $encryptionSettings
     * @return JweInterface
     */
    public function read(string $token, EncryptionSettingsInterface $encryptionSettings): JweInterface
    {
        if (!$encryptionSettings instanceof JweEncryptionJwks) {
            throw new JwtException('Can only work with JWK encryption settings for JWE tokens');
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
            /** @var int|null $recipientId */
            $jwe = $this->loader->loadAndDecryptWithKeySet($token, $jwkSet, $recipientId);
        } catch (\Throwable $exception) {
            throw new EncryptionException('Failed to decrypt JWE token.', 0, $exception);
        }
        if ($recipientId) {
            throw new EncryptionException('Failed to decrypt JWE token.');
        }
        $recipientHeader = $jwe->getRecipient($recipientId)->getHeader();

        return $this->jweFactory->create(
            $jwe->getSharedProtectedHeader(),
            $jwe->getPayload() ?? '',
            $jwe->getSharedHeader() ? $jwe->getSharedHeader() : null,
            $recipientHeader ? $recipientHeader : null
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
            $jwe = $this->serializer->unserialize($token);
        } catch (\Throwable $exception) {
            throw new JwtException('Failed to read JWE headers');
        }
        $headers = [];
        $headersValues = [];
        if ($jwe->getSharedHeader()) {
            $headersValues[] = $jwe->getSharedHeader();
        }
        if ($jwe->getSharedProtectedHeader()) {
            $headersValues[] = $jwe->getSharedProtectedHeader();
        }
        foreach ($jwe->getRecipients() as $recipient) {
            if ($recipient->getHeader()) {
                $headersValues[] = $recipient->getHeader();
            }
        }
        foreach ($headersValues as $headerValues) {
            $params = [];
            foreach ($headerValues as $header => $value) {
                $params[] = new Header($header, $value, null);
            }
            if ($params) {
                $headers[] = new JweHeader($params);
            }
        }

        return $headers;
    }

    private function validateJweSettings(JweInterface $jwe, EncryptionSettingsInterface $encryptionSettings): void
    {
        if (!$encryptionSettings instanceof JweEncryptionJwks) {
            throw new JwtException('Can only work with JWK encryption settings for JWE tokens');
        }
        if ($jwe->getPerRecipientUnprotectedHeaders()
            && count($encryptionSettings->getJwkSet()->getKeys()) !== count($jwe->getPerRecipientUnprotectedHeaders())
        ) {
            throw new EncryptionException('Not enough JWKs to encrypt all headers');
        }
        if (count($encryptionSettings->getJwkSet()->getKeys()) > 1 && !$jwe->getPerRecipientUnprotectedHeaders()) {
            throw new MalformedTokenException('Need more per-recipient headers for the amount of keys');
        }
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
