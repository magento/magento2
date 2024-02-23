<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Framework\App\Request\Http;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Collects policies auto-defined by Subresource Integrity.
 */
class SubresourceIntegrityCollector implements PolicyCollectorInterface
{
    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @param Http $request
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     */
    public function __construct(
        Http $request,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool
    ) {
        $this->request = $request;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $integrityHashes = [];

        $integrityRepository = $this->integrityRepositoryPool->get(
            $this->request->getFullActionName()
        );

        foreach ($integrityRepository->getAll() as $integrity) {
            $hashParts = explode("-", $integrity->getHash());

            if (is_array($hashParts) && count($hashParts) > 1) {
                $integrityHashes[$hashParts[1]] = $hashParts[0];
            }
        }

        if ($integrityHashes) {
            $defaultPolicies[] = new FetchPolicy(
                "script-src",
                false,
                [],
                [],
                false,
                false,
                false,
                [],
                $integrityHashes,
                false,
                false
            );
        }

        return $defaultPolicies;
    }
}
