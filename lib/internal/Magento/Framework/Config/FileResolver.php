<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\Fallback\RulePool;

/**
 * Class FileResolver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class FileResolver implements \Magento\Framework\Config\FileResolverInterface, DesignResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     * @since 2.0.0
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     * @since 2.0.0
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\View\DesignInterface
     * @since 2.0.0
     */
    protected $currentTheme;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $area;

    /**
     * @var Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     * @since 2.0.0
     */
    protected $resolver;

    /**
     * @param DirReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
     * @since 2.0.0
     */
    public function __construct(
        DirReader $moduleReader,
        FileIteratorFactory $iteratorFactory,
        DesignInterface $designInterface,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        ResolverInterface $resolver
    ) {
        $this->directoryList = $directoryList;
        $this->iteratorFactory = $iteratorFactory;
        $this->moduleReader = $moduleReader;
        $this->currentTheme = $designInterface->getDesignTheme();
        $this->area = $designInterface->getArea();
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->moduleReader->getConfigurationFiles($filename)->toArray();
                $themeConfigFile = $this->currentTheme->getCustomization()->getCustomViewConfigPath();
                if ($themeConfigFile
                    && $this->rootDirectory->isExist($this->rootDirectory->getRelativePath($themeConfigFile))
                ) {
                    $iterator[$this->rootDirectory->getRelativePath($themeConfigFile)] =
                        $this->rootDirectory->readFile(
                            $this->rootDirectory->getRelativePath(
                                $themeConfigFile
                            )
                        );
                } else {
                    $designPath = $this->resolver->resolve(
                        RulePool::TYPE_FILE,
                        'etc/view.xml',
                        $this->area,
                        $this->currentTheme
                    );
                    if (file_exists($designPath)) {
                        try {
                            $designDom = new \DOMDocument;
                            $designDom->load($designPath);
                            $iterator[$designPath] = $designDom->saveXML();
                        } catch (\Exception $e) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                new \Magento\Framework\Phrase('Could not read config file')
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
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getParents($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->moduleReader->getConfigurationFiles($filename)->toArray();
                $designPath = $this->resolver->resolve(
                    RulePool::TYPE_FILE,
                    'etc/view.xml',
                    $this->area,
                    $this->currentTheme
                );

                if (file_exists($designPath)) {
                    try {
                        $iterator = $this->getParentConfigs($this->currentTheme, []);
                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            new \Magento\Framework\Phrase('Could not read config file')
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
     * Recursively add parent theme configs
     *
     * @param ThemeInterface $theme
     * @param array $iterator
     * @param int $index
     * @return array
     * @since 2.1.0
     */
    private function getParentConfigs(ThemeInterface $theme, array $iterator, $index = 0)
    {
        if ($theme->getParentTheme() && $theme->isPhysical()) {
            $parentDesignPath = $this->resolver->resolve(
                RulePool::TYPE_FILE,
                'etc/view.xml',
                $this->area,
                $theme->getParentTheme()
            );

            $parentDom = new \DOMDocument;
            $parentDom->load($parentDesignPath);

            $iterator[$index] = $parentDom->saveXML();

            $iterator = $this->getParentConfigs($theme->getParentTheme(), $iterator, ++$index);
        }

        return $iterator;
    }
}
