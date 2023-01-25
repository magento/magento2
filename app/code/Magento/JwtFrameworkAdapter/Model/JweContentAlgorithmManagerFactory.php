<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128GCM;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A192CBCHS384;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A192GCM;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;

class JweContentAlgorithmManagerFactory
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
            new A128CBCHS256(),
            new A192CBCHS384(),
            new A256CBCHS512(),
            new A128GCM(),
            new A192GCM(),
            new A256GCM(),
        ]);
    }
}
