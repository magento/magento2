<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\Serializer\JWESerializerManager;

class JweLoaderFactory
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
     * Return object of type JWELoader
     *
     * @return JWELoader
     */
    public function create(): JWELoader
    {
        $allAlgorithms = array_merge(
            $this->algoManager->all(),
            $this->contentAlgoManager->all()
        );
        return new JWELoader(
            $this->serializers,
            new JWEDecrypter(new \Jose\Component\Core\AlgorithmManager($allAlgorithms)),
            null
        );
    }
}
