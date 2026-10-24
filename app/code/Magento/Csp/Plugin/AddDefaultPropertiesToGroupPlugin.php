<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Request\Http;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;
use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to add integrity to assets on page load.
 */
class AddDefaultPropertiesToGroupPlugin
{
    /**
     * @var State
     * @deprecated Hash resolution logic has been refactored to use HashResolverInterface.
     * @see HashResolverInterface
     */
    private State $state;

    /**
     * @var SubresourceIntegrityRepositoryPool
     * @deprecated Hash resolution logic has been refactored to use HashResolverInterface.
     * @see HashResolverInterface
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
     * @var HashResolverInterface
     */
    private HashResolverInterface $hashResolver;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var AssetConfig
     */
    private AssetConfig $assetConfig;

    /**
     * @param State $state
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     * @param Http|null $request
     * @param SriEnabledActions|null $action
     * @param HashResolverInterface|null $hashResolver
     * @param LoggerInterface|null $logger
     * @param AssetConfig|null $assetConfig
     */
    public function __construct(
        State $state,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool,
        ?Http $request = null,
        ?SriEnabledActions $action = null,
        ?HashResolverInterface $hashResolver = null,
        ?LoggerInterface $logger = null,
        ?AssetConfig $assetConfig = null
    ) {
        $this->state = $state;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
        $this->request = $request ?? ObjectManager::getInstance()->get(Http::class);
        $this->action = $action ?? ObjectManager::getInstance()->get(SriEnabledActions::class);
        $this->hashResolver = $hashResolver ?? ObjectManager::getInstance()->get(HashResolverInterface::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->assetConfig = $assetConfig ?? ObjectManager::getInstance()->get(AssetConfig::class);
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
        try {
            if ($this->canExecute($asset)) {
                $hash = $this->hashResolver->getHashByPath($asset->getPath());

                if ($hash) {
                    $properties['attributes']['integrity'] = $hash;
                    $properties['attributes']['crossorigin'] = 'anonymous';
                }
            }
        } catch (\Exception $e) {
            // Skip adding SRI attributes on failure - assets still load normally
            $this->logger->warning(
                'SRI: Failed to get integrity hash for asset',
                [
                    'asset_path' => $asset->getPath(),
                    'exception' => $e->getMessage()
                ]
            );
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
        // When JS merging is enabled, individual assets are combined into a merged bundle.
        // Adding integrity attributes here would make each asset its own group (groups require
        // identical properties), breaking merging entirely. Merged files get their SRI hashes
        // via GenerateMergedAssetIntegrity + AddIntegrityToAssetHtml instead.
        return !$this->assetConfig->isMergeJsFiles() &&
            $asset instanceof LocalInterface &&
            $this->action->isPaymentPageAction(
                $this->request->getFullActionName()
            );
    }
}
