<?php
/**
 * Application config file resolver
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @param Reader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
     */
    public function __construct(
        Reader $moduleReader,
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
}
