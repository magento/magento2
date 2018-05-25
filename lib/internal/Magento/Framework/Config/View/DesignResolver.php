<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\View;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\DesignResolverInterface;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\Phrase;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Fallback resolver for design configurations.
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DesignResolver implements DesignResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var ModuleReader
     */
    private $moduleReader;

    /**
     * @var FileIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var ThemeInterface
     */
    private $currentTheme;

    /**
     * @var string
     */
    private $area;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ModuleReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $design
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ModuleReader $moduleReader,
        FileIteratorFactory $iteratorFactory,
        DesignInterface $design,
        Filesystem $filesystem,
        ResolverInterface $resolver
    ) {
        $this->moduleReader = $moduleReader;
        $this->iteratorFactory = $iteratorFactory;
        $this->currentTheme = $design->getDesignTheme();
        $this->area = $design->getArea();
        $this->filesystem = $filesystem;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case Area::AREA_GLOBAL:
                $iterator = $this->moduleReader->getConfigurationFiles($filename)->toArray();
                $themeConfigFile = $this->currentTheme->getCustomization()->getCustomViewConfigPath();
                $rootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

                if ($themeConfigFile
                    && $rootDirectory->isExist($rootDirectory->getRelativePath($themeConfigFile))
                ) {
                    $iterator[$rootDirectory->getRelativePath($themeConfigFile)] =
                        $rootDirectory->readFile(
                            $rootDirectory->getRelativePath(
                                $themeConfigFile
                            )
                        );
                } else {
                    $designPath = $this->resolver->resolve(
                        RulePool::TYPE_FILE,
                        ConfigInterface::CONFIG_FILE_NAME,
                        $this->area,
                        $this->currentTheme
                    );
                    if ($designPath && file_exists($designPath)) {
                        try {
                            $designDom = new \DOMDocument();
                            $designDom->load($designPath);
                            $iterator[$designPath] = $designDom->saveXML();
                        } catch (\Exception $e) {
                            throw new LocalizedException(
                                new Phrase('Could not read config file')
                            );
                        }
                    }
                }
                break;
            default:
                $iterator = $this->iteratorFactory->create([]);
                break;
        }

        return $iterator;
    }

    /**
     * @inheritdoc
     */
    public function getParents($filename, $scope)
    {
        switch ($scope) {
            case Area::AREA_GLOBAL:
                $iterator = $this->moduleReader->getConfigurationFiles($filename)->toArray();
                $designPath = $this->resolver->resolve(
                    RulePool::TYPE_FILE,
                    ConfigInterface::CONFIG_FILE_NAME,
                    $this->area,
                    $this->currentTheme
                );

                if ($designPath && file_exists($designPath)) {
                    try {
                        $iterator = $this->getParentConfigs($this->currentTheme, []);
                    } catch (\Exception $e) {
                        throw new LocalizedException(
                            new Phrase('Could not read config file')
                        );
                    }
                }
                break;
            default:
                $iterator = $this->iteratorFactory->create([]);
                break;
        }

        return $iterator;
    }

    /**
     * Recursively collect parent theme configs.
     *
     * @param ThemeInterface $theme Parent theme
     * @param array $iterator Config iterator
     * @param int $index Config index
     * @return array Array of inherited configs
     */
    private function getParentConfigs(ThemeInterface $theme, array $iterator, $index = 0): array
    {
        if ($theme->getParentTheme() && $theme->isPhysical()) {
            $parentDesignPath = $this->resolver->resolve(
                RulePool::TYPE_FILE,
                ConfigInterface::CONFIG_FILE_NAME,
                $this->area,
                $theme->getParentTheme()
            );

            $parentDom = new \DOMDocument();
            $parentDom->load($parentDesignPath);

            $iterator[$index] = $parentDom->saveXML();

            $iterator = $this->getParentConfigs($theme->getParentTheme(), $iterator, ++$index);
        }

        return $iterator;
    }
}
