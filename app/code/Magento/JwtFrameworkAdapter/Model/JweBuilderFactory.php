<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\JWESerializerManager;

class JweBuilderFactory
{
    /**
     * @var JWESerializerManager
     */
    private $serializers;

    /**
     * @var AlgorithmManager
     */
    private $algoManager;

    /**
     * @var AlgorithmManager
     */
    private $contentAlgoManager;

    /***
     * @param JweSerializerPoolFactory $serializerPoolFactory
     * @param JweAlgorithmManagerFactory $algorithmManagerFactory
     * @param JweContentAlgorithmManagerFactory $contentAlgoManagerFactory
     */
    public function __construct(
        JweSerializerPoolFactory $serializerPoolFactory,
        JweAlgorithmManagerFactory $algorithmManagerFactory,
        JweContentAlgorithmManagerFactory $contentAlgoManagerFactory
    ) {
        $this->serializers = $serializerPoolFactory->create();
        $this->algoManager = $algorithmManagerFactory->create();
        $this->contentAlgoManager = $contentAlgoManagerFactory->create();
    }

    /***
     * Will return object of JWEBuilder
     *
     * @return JWEBuilder
     */
    public function create(): JWEBuilder
    {
        // jwt-framework v4 expects a single AlgorithmManager containing BOTH key and content algorithms.
        $allAlgorithms = array_merge(
            $this->algoManager->all(),
            $this->contentAlgoManager->all()
        );
        return new JWEBuilder(new AlgorithmManager($allAlgorithms));
    }
}
