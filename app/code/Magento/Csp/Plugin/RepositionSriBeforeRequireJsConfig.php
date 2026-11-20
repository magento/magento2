<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Block\Html\Head\Config as RequireJsConfig;
use Magento\RequireJs\Model\FileManager;

/**
 * Repositions sri.js immediately before requirejs-config.js in the page asset collection.
 *
 * sri.js registers the RequireJS onNodeCreated callback that injects SRI integrity
 * attributes on dynamically loaded scripts. It must execute before RequireJS begins
 * resolving the dependency declarations compiled into requirejs-config.js; otherwise
 * a browser-cached load can start resolving deps before the handler is registered.
 */
class RepositionSriBeforeRequireJsConfig
{
    /**
     * Asset identifier for the SRI integrity script.
     *
     * @var string
     */
    private const SRI_JS_ID = 'Magento_Csp::js/sri.js';

    /**
     * @param PageConfig $pageConfig
     * @param FileManager $fileManager
     */
    public function __construct(
        private readonly PageConfig $pageConfig,
        private readonly FileManager $fileManager
    ) {
    }

    /**
     * Repositions sri.js immediately before requirejs-config.js.
     *
     * Runs after setLayout() completes, which internally calls _prepareLayout(),
     * so all RequireJS assets are already registered in the collection.
     *
     * @param RequireJsConfig $subject
     * @param RequireJsConfig $result
     * @return RequireJsConfig
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetLayout(RequireJsConfig $subject, RequireJsConfig $result): RequireJsConfig
    {
        $assetCollection = $this->pageConfig->getAssetCollection();

        if (!$assetCollection->has(self::SRI_JS_ID)) {
            return $result;
        }

        $requireJsConfigKey = $this->fileManager->createRequireJsConfigAsset()->getFilePath();
        $allAssetKeys = array_keys($assetCollection->getAll());
        $configIndex = array_search($requireJsConfigKey, $allAssetKeys, true);

        if ($configIndex === false || $configIndex === 0) {
            return $result;
        }

        $insertAfterKey = $allAssetKeys[$configIndex - 1];

        if ($insertAfterKey === self::SRI_JS_ID) {
            return $result;
        }

        $sriJsAsset = $assetCollection->getAll()[self::SRI_JS_ID];
        $assetCollection->remove(self::SRI_JS_ID);
        $assetCollection->insert(self::SRI_JS_ID, $sriJsAsset, $insertAfterKey);

        return $result;
    }
}
