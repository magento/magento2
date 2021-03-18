<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\JWSSerializerManager;

class JwsLoaderFactory
{
    /**
     * @var JWSSerializerManager
     */
    private $serializer;

    /**
     * @var AlgorithmManager
     */
    private $algoManager;

    public function __construct(
        JwsSerializerPoolFactory $serializerPoolFactory,
        JwsAlgorithmManagerFactory $algorithmManagerFactory
    ) {
        $this->serializer = $serializerPoolFactory->create();
        $this->algoManager = $algorithmManagerFactory->create();
    }

    public function create(): JWSLoader
    {
        return new JWSLoader(
            $this->serializer,
            new JWSVerifier($this->algoManager),
            null
        );
    }
}
