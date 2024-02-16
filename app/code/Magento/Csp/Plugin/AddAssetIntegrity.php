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
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;

/**
 * Plugin to add integrity to assets on page load
 */
class AddAssetIntegrity
{
    /**
     * Expected asset content type.
     *
     * @var string
     */
    private const CONTENT_TYPE = 'js';

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @var array $controllerActions
     */
    private array $controllerActions;

    /**
     * @param Http $request
     * @param SubresourceIntegrityRepository $integrityRepository
     * @param array $controllerActions
     */
    public function __construct(
        Http $request,
        SubresourceIntegrityRepository $integrityRepository,
        array $controllerActions = []
    ) {
        $this->request = $request;
        $this->integrityRepository = $integrityRepository;
        $this->controllerActions = $controllerActions;
    }

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ): array {
        if ($asset instanceof LocalInterface) {
            if (in_array($this->request->getFullActionName(), $this->controllerActions)) {
                if ($asset->getContentType() === self::CONTENT_TYPE) {
                    $integrity = $this->integrityRepository->getByUrl(
                        $asset->getUrl()
                    );

                    if ($integrity) {
                        $properties['attributes']['integrity'] = $integrity->getHash();
                        $properties['attributes']['crossorigin'] = 'anonymous';
                    }
                }
            }
        }

        return [$asset, $properties];
    }
}
