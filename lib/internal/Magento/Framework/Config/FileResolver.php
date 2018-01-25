<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
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
     * @deprecated
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     * @deprecated
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\View\DesignInterface
     * @deprecated
     */
    protected $currentTheme;

    /**
     * @var string
     * @deprecated
     */
    protected $area;

    /**
     * @var Filesystem\Directory\ReadInterface
     * @deprecated
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     * @deprecated
     */
    protected $resolver;

    /**
     * @var DesignResolverInterface
     * @deprecated
     */
    private $designResolver;

    /**
     * @param DirReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DesignInterface $designInterface
     * @param DirectoryList $directoryList @deprecated
     * @param Filesystem $filesystem
     * @param ResolverInterface $resolver
     * @param DesignResolverInterface $designResolver
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DirReader $moduleReader,
        FileIteratorFactory $iteratorFactory,
        DesignInterface $designInterface,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        ResolverInterface $resolver,
        DesignResolverInterface $designResolver = null
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->moduleReader = $moduleReader;
        $this->currentTheme = $designInterface->getDesignTheme();
        $this->area = $designInterface->getArea();
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->resolver = $resolver;
        $this->designResolver = $designResolver ?: ObjectManager::getInstance()->get(DesignResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        return $this->designResolver->get($filename, $scope);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     */
    public function getParents($filename, $scope)
    {
        return $this->designResolver->getParents($filename, $scope);
    }
}
