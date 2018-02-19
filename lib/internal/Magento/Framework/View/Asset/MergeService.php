<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\Merged as ViewAssetMerged;
use Magento\Framework\View\Asset\MergeStrategy\Checksum as AssetMergeStrategyChecksum;
use Magento\Framework\View\Asset\MergeStrategy\FileExists as AssetMergeStrategyFileExists;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Service model responsible for making a decision of whether to use the merged asset in place of original ones
 */
class MergeService
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Config
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * State
     *
     * @var AppState
     */
    protected $state;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     * @param ScopeResolverInterface $scopeResolver
     * @param Filesystem $filesystem
     * @param AppState $state
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        ScopeResolverInterface $scopeResolver,
        Filesystem $filesystem,
        AppState $state
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->state = $state;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Return merged assets, if merging is enabled for a given content type
     *
     * @param MergeableInterface[] $assets
     * @param string $contentType
     *
     * @return array|\Iterator
     *
     * @throws InvalidArgumentException
     */
    public function getMergedAssets(array $assets, $contentType)
    {
        $isCss = $contentType == 'css';
        $isJs = $contentType == 'js';

        if (!$isCss && !$isJs) {
            throw new InvalidArgumentException("Merge for content type '{$contentType}' is not supported.");
        }

        $scopeType = $this->isAdminStore() ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORE;

        $isCssMergeEnabled = $this->config->isMergeCssFiles($scopeType);
        $isJsMergeEnabled = $this->config->isMergeJsFiles($scopeType);

        if (($isCss && $isCssMergeEnabled) || ($isJs && $isJsMergeEnabled)) {
            $mergeStrategyClass = AssetMergeStrategyFileExists::class;

            if (AppState::MODE_DEVELOPER === $this->state->getMode()) {
                $mergeStrategyClass = AssetMergeStrategyChecksum::class;
            }

            $mergeStrategy = $this->objectManager->get($mergeStrategyClass);

            $assets = $this->objectManager->create(
                ViewAssetMerged::class,
                ['assets' => $assets, 'mergeStrategy' => $mergeStrategy]
            );
        }

        return $assets;
    }

    /**
     * Remove all merged js/css files
     *
     * @return void
     */
    public function cleanMergedJsCss()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW)
            ->delete(Merged::getRelativeDir());
    }

    /**
     * Check is request use admin scope
     *
     * @return bool
     */
    protected function isAdminStore()
    {
        return Store::ADMIN_CODE == $this->scopeResolver->getScope()->getCode();
    }
}
