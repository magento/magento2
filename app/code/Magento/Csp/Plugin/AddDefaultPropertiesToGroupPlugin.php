<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Plugin to add integrity to assets on page load.
 */
class AddDefaultPropertiesToGroupPlugin
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
        $integrityRepository = $this->integrityRepositoryPool->get(
            $this->request->getFullActionName()
        );

        $integrity = $integrityRepository->getByUrl($asset->getUrl());

        if ($integrity && $integrity->getHash()) {
            $properties['attributes']['integrity'] = $integrity->getHash();
            $properties['attributes']['crossorigin'] = 'anonymous';
        }

        return [$asset, $properties];
    }
}
