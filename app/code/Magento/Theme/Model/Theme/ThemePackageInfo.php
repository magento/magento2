<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Theme\Model\Theme;

/**
 * Maps package name to full theme path, and vice versa
 */
class ThemePackageInfo
{
    /**
     * @var array
     */
    private $packageNameToFullPathMap = [];

    /**
     * Initialize dependencies.
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readDirFactory
     * @param Json|null $serializer
     */
    public function __construct(
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly ReadFactory $readDirFactory,
        private ?Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Get package name of a theme by its full theme path
     *
     * @param string $themePath
     * @return string
     */
    public function getPackageName($themePath)
    {
        $themePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);
        $themeDir = $this->readDirFactory->create($themePath);
        if ($themeDir->isExist('composer.json')) {
            $rawData = [];
            $themeFile = $themeDir->readFile('composer.json');
            if ($themeFile) {
                $rawData = $this->serializer->unserialize($themeFile);
            }
            return $rawData['name'] ?? '';
        }
        return '';
    }

    /**
     * Get full theme path by its package name
     *
     * @param string $packageName
     * @return string
     */
    public function getFullThemePath($packageName)
    {
        if (empty($this->packageNameToFullPathMap)) {
            $this->initializeMap();
        }
        return $this->packageNameToFullPathMap[$packageName] ?? '';
    }

    /**
     * Initialize package name to full theme path map
     *
     * @return void
     */
    private function initializeMap()
    {
        $themePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::THEME);
        /** @var Theme $theme */
        foreach ($themePaths as $fullThemePath => $themeDir) {
            $themeDirRead = $this->readDirFactory->create($themeDir);
            if ($themeDirRead->isExist('composer.json')) {
                $rawData = $this->serializer->unserialize($themeDirRead->readFile('composer.json'));
                if (isset($rawData['name'])) {
                    $this->packageNameToFullPathMap[$rawData['name']] = $fullThemePath;
                }
            }
        }
    }
}
