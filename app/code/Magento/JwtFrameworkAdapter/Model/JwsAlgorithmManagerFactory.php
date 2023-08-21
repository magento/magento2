<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\None;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\PS384;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JwsAlgorithmManagerFactory
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
            new HS256(),
            new HS384(),
            new HS512(),
            new RS256(),
            new RS384(),
            new RS512(),
            new PS256(),
            new PS384(),
            new PS512(),
            new ES256(),
            new ES384(),
            new ES512(),
            new EdDSA(),
            new None(),
        ]);
    }
}
