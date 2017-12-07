<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Class FileResolver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileResolver implements \Magento\Framework\Config\FileResolverInterface, DesignResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $currentTheme;

    /**
     * @var string
     */
    protected $area;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     */
    protected $resolver;

    /**
     * @var DirectoryList
     * @deprecated Unused class property
     */
    private $directoryList;

    /**
     * @param DirReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList @deprecated
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
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
                            $designDom = new \DOMDocument();
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

            $parentDom = new \DOMDocument();
            $parentDom->load($parentDesignPath);

            $iterator[$index] = $parentDom->saveXML();

            $iterator = $this->getParentConfigs($theme->getParentTheme(), $iterator, ++$index);
        }

        return $iterator;
    }
}
