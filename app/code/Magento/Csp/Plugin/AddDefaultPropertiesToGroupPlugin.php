<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\State;
use Magento\Deploy\Package\Package;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Plugin to add integrity to assets on page load.
 */
class AddDefaultPropertiesToGroupPlugin
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @param State $state
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     */
    public function __construct(
        State $state,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool
    ) {
        $this->state = $state;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
    }

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ): array {
        if ($asset instanceof LocalInterface) {
            $integrityRepository = $this->integrityRepositoryPool->get(
                Package::BASE_AREA
            );

            $integrity = $integrityRepository->getByPath($asset->getPath());

            if (!$integrity) {
                $integrityRepository = $this->integrityRepositoryPool->get(
                    $this->state->getAreaCode()
                );

                $integrity = $integrityRepository->getByPath($asset->getPath());
            }

            if ($integrity && $integrity->getHash()) {
                $properties['attributes']['integrity'] = $integrity->getHash();
                $properties['attributes']['crossorigin'] = 'anonymous';
            }
        }

        return [$asset, $properties];
    }
}
