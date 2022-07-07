<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\Collector\CspWhitelistXml;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\CustomizationInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirectoryRead;
use Magento\Framework\Config\CompositeFileIteratorFactory;

/**
 * Combines configuration files from both modules and current theme.
 */
class FileResolver implements FileResolverInterface
{
    /**
     * @var FileResolverInterface
     */
    private $moduleFileResolver;

    /**
     * @var ThemeInterface
     */
    private $theme;

    /**
     * @var CustomizationInterfaceFactory
     */
    private $themeInfoFactory;

    /**
     * @var DirectoryRead
     */
    private $rootDir;

    /**
     * @var CompositeFileIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @param FileResolverInterface $moduleFileResolver
     * @param DesignInterface $design
     * @param CustomizationInterfaceFactory $customizationFactory
     * @param Filesystem $filesystem
     * @param CompositeFileIteratorFactory $iteratorFactory
     */
    public function __construct(
        FileResolverInterface $moduleFileResolver,
        DesignInterface $design,
        CustomizationInterfaceFactory $customizationFactory,
        Filesystem $filesystem,
        CompositeFileIteratorFactory $iteratorFactory
    ) {
        $this->moduleFileResolver = $moduleFileResolver;
        $this->theme = $design->getDesignTheme();
        $this->themeInfoFactory = $customizationFactory;
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * @inheritDoc
     */
    public function get($filename, $scope)
    {
         $configs = $this->moduleFileResolver->get($filename, $scope);
        if ($scope === 'global') {
            $files = [];
            $theme = $this->theme;
            while ($theme) {
                /** @var CustomizationInterface $info */
                $info = $this->themeInfoFactory->create(['theme' => $theme]);
                $file = $info->getThemeFilesPath() .'/etc/' .$filename;
                if ($this->rootDir->isExist($file)) {
                    $files[] = $file;
                }
                $theme = $theme->getParentTheme();
            }
            $configs = $this->iteratorFactory->create(
                ['paths' => array_reverse($files), 'existingIterator' => $configs]
            );
        }

        return $configs;
    }
}
