<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Signature\JWSVerifierFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer as JwsFlatSerializer;
use Jose\Component\Signature\Serializer\JSONGeneralSerializer as JwsJsonSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManagerFactory;
use Jose\Easy\AlgorithmProvider;

class JwsLoaderFactory
{
    public function create()
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
        $jwsAlgorithmFactory = new AlgorithmManagerFactory();
        foreach ($jwsAlgorithmProvider->getAvailableAlgorithms() as $algorithm) {
            $jwsAlgorithmFactory->add($algorithm->name(), $algorithm);
        }
        $jwsSerializerFactory = new JWSSerializerManagerFactory();
        $jwsSerializerFactory->add(new CompactSerializer());
        $jwsSerializerFactory->add(new JwsJsonSerializer());
        $jwsSerializerFactory->add(new JwsFlatSerializer());

        return new \Jose\Component\Signature\JWSLoaderFactory(
            $jwsSerializerFactory,
            new JWSVerifierFactory($jwsAlgorithmFactory),
            null
        );
    }
}
