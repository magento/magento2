<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Deploy\Package\Package;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Request\Http;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;

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
     * @var Http
     */
    private Http $request;

    /**
     * @var SriEnabledActions
     */
    private SriEnabledActions $action;

    /**
     * @param State $state
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     * @param Http|null $request
     * @param SriEnabledActions|null $action
     */
    public function __construct(
        State $state,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool,
        ?Http $request = null,
        ?SriEnabledActions $action = null
    ) {
        $this->state = $state;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
        $this->request = $request ?? ObjectManager::getInstance()->get(Http::class);
        $this->action = $action ?? ObjectManager::getInstance()->get(SriEnabledActions::class);
    }

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ): array {
        if ($this->canExecute($asset)) {
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

    /**
     * Check if beforeGetFilteredProperties plugin should execute
     *
     * @param AssetInterface $asset
     * @return bool
     */
    private function canExecute(AssetInterface $asset): bool
    {
        return $asset instanceof LocalInterface &&
            $this->action->isPaymentPageAction(
                $this->request->getFullActionName()
            );
    }
}
