<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
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

    /**
     * @var CompressionMethodManager
     */
    private $compressionManager;

    public function __construct(
        JweSerializerPoolFactory $serializerPoolFactory,
        JweAlgorithmManagerFactory $algorithmManagerFactory,
        JweContentAlgorithmManagerFactory $contentAlgoManagerFactory,
        JweCompressionManagerFactory $compressionManagerFactory
    ) {
        $this->serializers = $serializerPoolFactory->create();
        $this->algoManager = $algorithmManagerFactory->create();
        $this->contentAlgoManager = $contentAlgoManagerFactory->create();
        $this->compressionManager = $compressionManagerFactory->create();
    }

    public function create(): JWELoader
    {
        return new JWELoader(
            $this->serializers,
            new JWEDecrypter(
                $this->algoManager,
                $this->contentAlgoManager,
                $this->compressionManager
            ),
            null
        );
    }
}
