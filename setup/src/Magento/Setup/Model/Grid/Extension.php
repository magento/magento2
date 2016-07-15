<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
/**
 * Extension Grid
 */
class Extension
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var array
     */
    private $lastSyncData;

    /**
     * @param ComposerInformation $composerInformation
     * @param TypeMapper $typeMapper
     */
    public function __construct(
        ComposerInformation $composerInformation,
        TypeMapper $typeMapper
    ) {
        $this->composerInformation = $composerInformation;
        $this->typeMapper = $typeMapper;
    }

    /**
     * Get formatted list of installed extensions
     *
     * @return array
     */
    public function getList()
    {
        $extensions = $this->getInstalledExtensions();

        foreach ($extensions as &$extension) {
            $extension['update'] = false;
            if (isset($this->lastSyncData['packages'][$extension['name']]['latestVersion'])
                && version_compare(
                    $this->lastSyncData['packages'][$extension['name']]['latestVersion'],
                    $extension['version'],
                    '>'
                )) {
                $extension['update'] = true;
            }
            $parts = explode('/', $extension['name']);
            $extension['vendor'] = ucfirst($parts[0]);
            $extension['type'] = $this->typeMapper->map($extension['name'], $extension['type']);
        }

        return array_values($extensions);
    }

    /**
     * Retrieve list of installed extensions
     *
     * @return array
     */
    public function getInstalledExtensions()
    {
        return array_intersect_key(
            $this->composerInformation->getInstalledMagentoPackages(),
            $this->composerInformation->getRootPackage()->getRequires()
        );
    }

    /**
     * @param array $lastSyncData
     */
    public function setLastSyncData($lastSyncData)
    {
        $this->lastSyncData = $lastSyncData;
    }
}
