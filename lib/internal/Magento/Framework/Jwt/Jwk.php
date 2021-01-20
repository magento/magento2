<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWK.
 *
 * Signature/encryption settings for JWTs.
 */
class Jwk
{
    public const PUBLIC_KEY_USE_SIGNATURE = 'sig';

    public const PUBLIC_KEY_USE_ENCRYPTION = 'enc';

    public const KEY_OP_SIGN = 'sign';

    public const KEY_OP_VERIFY = 'verify';

    public const KEY_OP_ENCRYPT = 'encrypt';

    public const KEY_OP_DECRYPT = 'decrypt';

    public const KEY_OP_WRAP_KEY = 'wrapKey';

    public const KEY_OP_UNWRAP_KEY = 'unwrapKey';

    public const KEY_OP_DERIVE_KEY = 'deriveKey';

    public const KEY_OP_DERIVE_BITS = 'deriveBits';

    /**
     * @var string
     */
    private $kty;

    /**
     * @var string|null
     */
    private $use;

    /**
     * @var string[]|null
     */
    private $keyOps;

    /**
     * @var string|null
     */
    private $alg;

    /**
     * @var string|null
     */
    private $x5u;

    /**
     * @var string[]|null
     */
    private $x5c;

    /**
     * @var string|null
     */
    private $x5t;

    /**
     * @var string|null
     */
    private $x5ts256;

    /**
     * @var array
     */
    private $data;

    /**
     * Jwk constructor.
     * @param string $kty
     * @param array $data
     * @param string|null $use
     * @param string[]|null $keyOps
     * @param string|null $alg
     * @param string|null $x5u
     * @param string[]|null $x5c
     * @param string|null $x5t
     * @param string|null $x5ts256
     */
    public function __construct(
        string $kty,
        array $data,
        ?string $use = null,
        ?array $keyOps = null,
        ?string $alg = null,
        ?string $x5u = null,
        ?array $x5c = null,
        ?string $x5t = null,
        ?string $x5ts256 = null
    ) {
        $this->kty = $kty;
        $this->data = $data;
        $this->use = $use;
        $this->keyOps = $keyOps;
        $this->alg = $alg;
        $this->x5u = $x5u;
        $this->x5c = $x5c;
        $this->x5t = $x5t;
        $this->x5ts256 = $x5ts256;
    }

    /**
     * "kty" parameter.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return $this->kty;
    }

    /**
     * "use" parameter.
     *
     * @return string|null
     */
    public function getPublicKeyUse(): ?string
    {
        return $this->use;
    }

    /**
     * "key_ops" parameter.
     *
     * @return string[]|null
     */
    public function getKeyOperations(): ?array
    {
        return $this->keyOps;
    }

    /**
     * "alg" parameter.
     *
     * @return string|null
     */
    public function getAlgorithm(): ?string
    {
        return $this->alg;
    }

    /**
     * "x5u" parameter.
     *
     * @return string|null
     */
    public function getX509Url(): ?string
    {
        return $this->x5u;
    }

    /**
     * "x5c" parameter.
     *
     * @return string[]|null
     */
    public function getX509CertificateChain(): ?array
    {
        return $this->x5c;
    }

    /**
     * "x5t" parameter.
     *
     * @return string|null
     */
    public function getX509Sha1Thumbprint(): ?string
    {
        return $this->x5t;
    }

    /**
     * "x5t#S256" parameter.
     *
     * @return string|null
     */
    public function getX509Sha256Thumbprint(): ?string
    {
        return $this->x5ts256;
    }

    /**
     * Map with algorithm (type) specific data.
     *
     * @return string[]
     */
    public function getAlgoData(): array
    {
        return $this->data;
    }
}
