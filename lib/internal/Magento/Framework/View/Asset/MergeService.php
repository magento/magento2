<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ScopeResolverInterface;
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
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * State
     *
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     * @param ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\State $state
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
     * @throws \InvalidArgumentException
     */
    public function getMergedAssets(array $assets, $contentType)
    {
        $isCss = $contentType == 'css';
        $isJs = $contentType == 'js';

        if (!$isCss && !$isJs) {
            throw new \InvalidArgumentException("Merge for content type '{$contentType}' is not supported.");
        }

        $scopeType = $this->isAdminStore() ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORE;

        $isCssMergeEnabled = $this->config->isMergeCssFiles($scopeType);
        $isJsMergeEnabled = $this->config->isMergeJsFiles($scopeType);

        if (($isCss && $isCssMergeEnabled) || ($isJs && $isJsMergeEnabled)) {
            $mergeStrategyClass = \Magento\Framework\View\Asset\MergeStrategy\FileExists::class;

            if ($this->state->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
                $mergeStrategyClass = \Magento\Framework\View\Asset\MergeStrategy\Checksum::class;
            }

            $mergeStrategy = $this->objectManager->get($mergeStrategyClass);

            $assets = $this->objectManager->create(
                \Magento\Framework\View\Asset\Merged::class,
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
