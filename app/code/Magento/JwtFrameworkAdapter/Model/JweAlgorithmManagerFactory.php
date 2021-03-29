<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;

class JweAlgorithmManagerFactory
{
    private const ALGOS = [
        \Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A128KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A192KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\Dir::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHES::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA128KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA192KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA256KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A128GCMKW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A192GCMKW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\A256GCMKW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS256A128KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS384A192KW::class,
        \Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS512A256KW::class
    ];

    /**
     * @var AlgorithmProviderFactory
     */
    private $algorithmProviderFactory;

    public function __construct(AlgorithmProviderFactory $algorithmProviderFactory) {
        $this->algorithmProviderFactory = $algorithmProviderFactory;
    }

    public function create(): AlgorithmManager
    {
        return new AlgorithmManager($this->algorithmProviderFactory->create(self::ALGOS)->getAvailableAlgorithms());
    }
}
