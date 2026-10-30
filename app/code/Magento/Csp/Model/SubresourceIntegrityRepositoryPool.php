<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

/**
 * Pool of subresource integrity repositories.
 */
class SubresourceIntegrityRepositoryPool
{
    /**
     * @var array
     */
    private array $repositories = [];

    /**
     * @var SubresourceIntegrityRepositoryFactory
     */
    private SubresourceIntegrityRepositoryFactory $integrityRepositoryFactory;

    /**
     * @param SubresourceIntegrityRepositoryFactory $integrityRepositoryFactory
     */
    public function __construct(
        SubresourceIntegrityRepositoryFactory $integrityRepositoryFactory
    ) {
        $this->integrityRepositoryFactory = $integrityRepositoryFactory;
    }

    /**
     * Gets subresource integrity repository by given context.
     *
     * @param string $context
     *
     * @return SubresourceIntegrityRepository
     */
    public function get(string $context): SubresourceIntegrityRepository
    {
        if (!isset($this->repositories[$context])) {
            $this->repositories[$context] = $this->integrityRepositoryFactory->create(
                [
                    "context" => $context
                ]
            );
        }

        return $this->repositories[$context];
    }
}
