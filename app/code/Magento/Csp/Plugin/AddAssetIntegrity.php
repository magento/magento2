<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\Asset\SubResourceIntegrity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\App\Request\Http;

/**
 * Plugin to add integrity to assets on page load
 */
class AddAssetIntegrity
{
    /**
     * @var GroupedCollection
     */
    private GroupedCollection $groupedCollection;

    /**
     * @var SubResourceIntegrity
     */
    private SubResourceIntegrity $resourceIntegrity;

    /**
     * Constant for asset content type
     */
    private const CONTENT_TYPE = 'js';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var array $controllerActions
     */
    private array $controllerActions;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * constructor
     *
     * @param GroupedCollection $groupedCollection
     * @param SubResourceIntegrity $resourceIntegrity
     * @param Filesystem $fileSystem
     * @param Http $request
     * @param array $controllerActions
     */
    public function __construct(
        GroupedCollection $groupedCollection,
        SubResourceIntegrity $resourceIntegrity,
        Filesystem $fileSystem,
        Http $request,
        array $controllerActions = []
    ) {
        $this->groupedCollection = $groupedCollection;
        $this->resourceIntegrity = $resourceIntegrity;
        $this->filesystem = $fileSystem;
        $this->request = $request;
        $this->controllerActions = $controllerActions;
    }

    /**
     * Before Plugin to add Properties to JS assets
     *
     * @param GroupedCollection $subject
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws FileSystemException
     */
    public function beforeGetFilteredProperties(
        GroupedCollection $subject,
        AssetInterface $asset,
        array $properties = []
    ):array {

        $path = $asset instanceof LocalInterface ? $asset->getpath(): $asset->getUrl();
        $actionName = $this->request->getFullActionName();
        if (in_array($actionName, $this->controllerActions) &&
            $this->checkFileExists($path)
        ) {
            $contentType = $asset->getContentType();
            $fileContent = $asset->getContent();
            $attributes = [];

            if ($fileContent && $contentType === self::CONTENT_TYPE) {
                $integrity = $this->resourceIntegrity->generateAssetIntegrity($path, $fileContent);
                if ($integrity) {
                    $attributes['integrity'] = $integrity;
                    $attributes['crossorigin'] = 'anonymous';
                    $properties['attributes'] = $attributes;
                }
            }
        }
        return [$asset, $properties];
    }

    /**
     * Check if file exist
     *
     * @param string $relPath
     * @return bool
     * @throws FileSystemException
     */
    private function checkFileExists($relPath)
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        return $dir->isExist($relPath);
    }
}
