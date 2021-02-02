<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;
use Jose\Easy\AlgorithmProvider;

class JwsBuilderFactory
{
    public function create(): JWSBuilder
    {
        $jwsAlgorithms = [
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
        ];
        $jwsAlgorithmProvider = new AlgorithmProvider($jwsAlgorithms);
        $algorithmManager = new AlgorithmManager($jwsAlgorithmProvider->getAvailableAlgorithms());

        return  new JWSBuilder($algorithmManager);
    }
}
