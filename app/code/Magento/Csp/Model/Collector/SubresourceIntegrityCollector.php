<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;

/**
 * Collects policies auto-defined by Subresource Integrity.
 */
class SubresourceIntegrityCollector implements PolicyCollectorInterface
{
    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @param SubresourceIntegrityRepository $integrityRepository
     */
    public function __construct(
        SubresourceIntegrityRepository $integrityRepository
    ) {
        $this->integrityRepository = $integrityRepository;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $integrityHashes = [];
        $assetIntegrity = $this->integrityRepository->getAll();

        foreach ($assetIntegrity as $integrity) {
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
