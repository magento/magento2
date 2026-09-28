<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Block\Sri;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Block for Subresource Integrity hashes rendering.
 *
 * @api
 */
class Hashes extends Template
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SubresourceIntegrityRepositoryPool
     * @deprecated
     * @see HashResolverInterface - SRI hashes are now retrieved directly from the resolver
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @var HashResolverInterface|null
     */
    private ?HashResolverInterface $hashResolver;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @param Context $context
     * @param array $data
     * @param SubresourceIntegrityRepositoryPool|null $integrityRepositoryPool
     * @param SerializerInterface|null $serializer
     * @param HashResolverInterface|null $hashResolver
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SubresourceIntegrityRepositoryPool $integrityRepositoryPool = null,
        ?SerializerInterface $serializer = null,
        ?HashResolverInterface $hashResolver = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($context, $data);

        $this->integrityRepositoryPool = $integrityRepositoryPool ?: ObjectManager::getInstance()
            ->get(SubresourceIntegrityRepositoryPool::class);

        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);

        $this->hashResolver = $hashResolver ?: ObjectManager::getInstance()
            ->get(HashResolverInterface::class);

        $this->logger = $logger ?? ObjectManager::getInstance()
            ->get(LoggerInterface::class);
    }

    /**
     * Retrieves integrity hashes in serialized format.
     *
     * @return string
     */
    public function getSerialized(): string
    {
        try {
            return $this->serializer->serialize($this->hashResolver->getAllHashes());
        } catch (\Exception $e) {
            // Return empty object on failure - checkout works without SRI
            $this->logger->warning(
                'SRI: Failed to retrieve hashes',
                ['exception' => $e->getMessage()]
            );
            return '{}';
        }
    }
}
