<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A128GCMKW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A128KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A192GCMKW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A192KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256GCMKW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHES;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA128KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA192KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\ECDHESA256KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS256A128KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS384A192KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\PBES2HS512A256KW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JweAlgorithmManagerFactory
{
    /**
     * @var AlgorithmProviderFactory
     */
    private AlgorithmProviderFactory $algorithmProviderFactory;

    /**
     * Default constructor.
     * @param AlgorithmProviderFactory $algorithmProviderFactory
     */
    public function __construct(AlgorithmProviderFactory $algorithmProviderFactory)
    {
        $this->algorithmProviderFactory = $algorithmProviderFactory;
    }

    /**
     * Returns the list of names of supported algorithms.
     *
     * @return AlgorithmManager
     */
    public function create(): AlgorithmManager
    {
        return new AlgorithmManager([
            new RSAOAEP(),
            new RSAOAEP256(),
            new A128KW(),
            new A192KW(),
            new A256KW(),
            new Dir(),
            new ECDHES(),
            new ECDHESA128KW(),
            new ECDHESA192KW(),
            new ECDHESA256KW(),
            new A128GCMKW(),
            new A192GCMKW(),
            new A256GCMKW(),
            new PBES2HS256A128KW(),
            new PBES2HS384A192KW(),
            new PBES2HS512A256KW(),
        ]);
    }
}
