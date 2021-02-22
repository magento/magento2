<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Easy\AlgorithmProvider;

class JwsAlgorithmManagerFactory
{
    private const ALGOS = [

        \Jose\Component\Signature\Algorithm\HS256::class,
        \Jose\Component\Signature\Algorithm\HS384::class,
        \Jose\Component\Signature\Algorithm\HS512::class,
        \Jose\Component\Signature\Algorithm\RS256::class,
        \Jose\Component\Signature\Algorithm\RS384::class,
        \Jose\Component\Signature\Algorithm\RS512::class,
        \Jose\Component\Signature\Algorithm\PS256::class,
        \Jose\Component\Signature\Algorithm\PS384::class,
        \Jose\Component\Signature\Algorithm\PS512::class,
        \Jose\Component\Signature\Algorithm\ES256::class,
        \Jose\Component\Signature\Algorithm\ES384::class,
        \Jose\Component\Signature\Algorithm\ES512::class,
        \Jose\Component\Signature\Algorithm\EdDSA::class,
        \Jose\Component\Signature\Algorithm\None::class
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
