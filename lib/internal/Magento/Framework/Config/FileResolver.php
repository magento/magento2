<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\Fallback\RulePool;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
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
    protected $themePath;

    /**
     * @var string
     */
    protected $area;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     */
    protected $resolverInterface;

    /**
     * @param Reader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param ComponentRegistrar $componentRegistrar
     * @param ResolverInterface $resolverInterface
     */
    public function __construct(
        Reader $moduleReader,
        FileIteratorFactory $iteratorFactory,
        DesignInterface $designInterface,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        ComponentRegistrar $componentRegistrar,
        ResolverInterface $resolverInterface
    ) {
        $this->directoryList = $directoryList;
        $this->iteratorFactory = $iteratorFactory;
        $this->moduleReader = $moduleReader;
        $this->currentTheme = $designInterface->getDesignTheme();
        $this->themePath = $designInterface->getThemePath($this->currentTheme);
        $this->area = $designInterface->getArea();
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->componentRegistrar = $componentRegistrar;
        $this->resolverInterface = $resolverInterface;
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
                    $designPath = $this->resolverInterface->resolve(
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
                                __('Could not read config file')
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
}
