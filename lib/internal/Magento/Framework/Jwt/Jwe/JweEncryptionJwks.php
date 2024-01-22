<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jwe;

use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkSet;

/**
 * JWK encryption settings.
 */
class JweEncryptionJwks implements JweEncryptionSettingsInterface
{
    /**
     * @var JwkSet
     */
    private $jwkSet;

    /**
     * @var string
     */
    private $contentAlgo;

    /**
     * @param JwkSet|Jwk $jwk
     * @param string $contentEncryptionAlgo
     */
    public function __construct($jwk, string $contentEncryptionAlgo)
    {
        if ($jwk instanceof Jwk) {
            $jwk = new JwkSet([$jwk]);
        }
        if (!$jwk instanceof JwkSet) {
            throw new \InvalidArgumentException('JWK has to be provided');
        }
        $this->jwkSet = $jwk;
        foreach ($this->jwkSet->getKeys() as $jwk) {
            $this->validateJwk($jwk);
        }
        $this->contentAlgo = $contentEncryptionAlgo;
    }

    /**
     * @inheritDoc
     */
    public function getAlgorithmName(): string
    {
        if (count($this->jwkSet->getKeys()) > 1) {
            return 'jwe-json-serialization';
        } else {
            return $this->jwkSet->getKeys()[0]->getAlgorithm();
        }
    }

    /**
     * @inheritDoc
     */
    public function getContentEncryptionAlgorithm(): string
    {
        return $this->contentAlgo;
    }

    /**
     * JWK Set.
     *
     * @return JwkSet
     */
    public function getJwkSet(): JwkSet
    {
        return $this->jwkSet;
    }

    /**
     * Validate JWK values.
     *
     * @param Jwk $jwk
     * @return void
     */
    private function validateJwk(Jwk $jwk): void
    {
        if ($jwk->getPublicKeyUse() === Jwk::PUBLIC_KEY_USE_SIGNATURE) {
            throw new EncryptionException('JWK is not meant for JWEs');
        }
    }
}
