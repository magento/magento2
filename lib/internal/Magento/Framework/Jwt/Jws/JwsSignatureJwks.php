<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Magento\Framework\Jwt\Exception\EncryptionException;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkSet;

/**
 * JWK signature settings.
 */
class JwsSignatureJwks implements JwsSignatureSettingsInterface
{
    /**
     * @var JwkSet
     */
    private $jwkSet;

    /**
     * @param JwkSet|Jwk $jwk
     */
    public function __construct($jwk)
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
    }

    /**
     * @inheritDoc
     */
    public function getAlgorithmName(): string
    {
        if (count($this->jwkSet->getKeys()) > 1) {
            return 'jws-json-serialization';
        } else {
            return $this->jwkSet->getKeys()[0]->getAlgorithm();
        }
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
        if ($jwk->getPublicKeyUse() === Jwk::PUBLIC_KEY_USE_ENCRYPTION) {
            throw new EncryptionException('JWK is not meant for JWSs');
        }
    }
}
