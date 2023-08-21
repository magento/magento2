<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * A service for preprocessing content of assets
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Source
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDir;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Pool
     */
    private $preProcessorPool;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile
     */
    protected $fallback;

    /**
     * @var \Magento\Framework\View\Design\Theme\ListInterface
     * @deprecated 100.0.2
     */
    private $themeList;

    /**
     * @var ChainFactoryInterface
     */
    private $chainFactory;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ReadFactory $readFactory
     * @param PreProcessor\Pool $preProcessorPool
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $fallback
     * @param \Magento\Framework\View\Design\Theme\ListInterface $themeList
     * @param ChainFactoryInterface $chainFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        ReadFactory $readFactory,
        PreProcessor\Pool $preProcessorPool,
        \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $fallback,
        \Magento\Framework\View\Design\Theme\ListInterface $themeList,
        ChainFactoryInterface $chainFactory
    ) {
        $this->filesystem = $filesystem;
        $this->readFactory = $readFactory;
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->tmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->preProcessorPool = $preProcessorPool;
        $this->fallback = $fallback;
        $this->themeList = $themeList;
        $this->chainFactory = $chainFactory;
    }

    /**
     * Get absolute path to the asset file
     *
     * @param LocalInterface $asset
     * @return bool|string
     */
    public function getFile(LocalInterface $asset)
    {
        $result = $this->preProcess($asset);
        if (!$result) {
            return false;
        }
        list($dir, $path) = $result;
        return $this->readFactory->create($dir)->getAbsolutePath($path);
    }

    /**
     * Get content of an asset
     *
     * @param LocalInterface $asset
     * @return bool|string
     */
    public function getContent(LocalInterface $asset)
    {
        $result = $this->preProcess($asset);
        if (!$result) {
            return false;
        }
        list($dir, $path) = $result;
        return $this->readFactory->create($dir)->readFile($path);
    }

    /**
     * Perform necessary preprocessing and materialization when the specified asset is requested
     *
     * Returns an array of two elements:
     * - directory where the file is supposed to be found
     * - relative path to the file
     *
     * returns false if source file was not found
     *
     * @param LocalInterface $asset
     * @return array|bool
     */
    private function preProcess(LocalInterface $asset)
    {
        $sourceFile = $this->findSourceFile($asset);
        $dir = $this->rootDir->getAbsolutePath();
        $path = '';
        if ($sourceFile) {
            $path = basename($sourceFile);
            $dir = dirname($sourceFile);
        }

        $chain = $this->createChain($asset, $dir, $path);
        $this->preProcessorPool->process($chain);
        $chain->assertValid();
        if ($chain->isChanged()) {
            $dir = $this->tmpDir->getAbsolutePath();
            $path = $chain->getTargetAssetPath();
            $this->tmpDir->writeFile($path, $chain->getContent());
        }
        if (empty($path)) {
            $result = false;
        } else {
            $result = [$dir, $path, $chain->getContentType()];
        }
        return $result;
    }

    /**
     * Method to get source content type.
     *
     * @param LocalInterface $asset
     * @return string
     */
    public function getSourceContentType(LocalInterface $asset)
    {
        list(, , $type) = $this->preProcess($asset);
        return $type;
    }

    /**
     * Method to find source.
     *
     * @param LocalInterface $asset
     * @return bool|string
     */
    public function findSource(LocalInterface $asset)
    {
        return $this->findSourceFile($asset);
    }

    /**
     * Infer a content type from the specified path
     *
     * @param string $path
     * @return string
     */
    public function getContentType($path)
    {
        return $path !== null ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';
    }

    /**
     * Search for asset file depending on its context type
     *
     * @param LocalInterface $asset
     * @return bool|string
     * @throws \LogicException
     */
    private function findSourceFile(LocalInterface $asset)
    {
        $context = $asset->getContext();
        if ($context instanceof \Magento\Framework\View\Asset\File\FallbackContext) {
            $result = $this->findFileThroughFallback($asset, $context);
        } elseif ($context instanceof \Magento\Framework\View\Asset\File\Context) {
            $result = $this->findFile($asset, $context);
        } else {
            $type = get_class($context);
            throw new \LogicException("Support for {$type} is not implemented.");
        }
        return $result;
    }

    /**
     * Find asset file via fallback mechanism
     *
     * @param LocalInterface $asset
     * @param \Magento\Framework\View\Asset\File\FallbackContext $context
     * @return bool|string
     */
    private function findFileThroughFallback(
        LocalInterface $asset,
        \Magento\Framework\View\Asset\File\FallbackContext $context
    ) {
        $themeModel = $this->getThemeProvider()->getThemeByFullPath(
            $context->getAreaCode() . '/' . $context->getThemePath()
        );
        $sourceFile = $this->fallback->getFile(
            $context->getAreaCode(),
            $themeModel,
            $context->getLocale(),
            $asset->getFilePath(),
            $asset->getModule()
        );
        return $sourceFile;
    }

    /**
     * Method to get theme provider.
     *
     * @return ThemeProviderInterface
     */
    private function getThemeProvider()
    {
        if (null === $this->themeProvider) {
            $this->themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        }

        return $this->themeProvider;
    }

    /**
     * Find asset file by simply appending its path to the directory in context
     *
     * @param LocalInterface $asset
     * @param \Magento\Framework\View\Asset\File\Context $context
     * @return string
     */
    private function findFile(LocalInterface $asset, \Magento\Framework\View\Asset\File\Context $context)
    {
        $dir = $this->filesystem->getDirectoryRead($context->getBaseDirType());
        Simple::assertFilePathFormat($asset->getFilePath());
        return $dir->getAbsolutePath($asset->getPath());
    }

    /**
     * Method to find relative source file path.
     *
     * @param \Magento\Framework\View\Asset\LocalInterface $asset
     *
     * @return bool|string
     * @deprecated 100.1.0 If custom vendor directory is outside Magento root,
     * then this method will return unexpected result.
     */
    public function findRelativeSourceFilePath(LocalInterface $asset)
    {
        $sourceFile = $this->findSourceFile($asset);
        if (!$sourceFile) {
            return false;
        }
        return $this->rootDir->getRelativePath($sourceFile);
    }

    /**
     * Creates a chain for pre-processing
     *
     * @param LocalInterface $asset
     * @param string|bool $dir
     * @param string|bool $path
     * @return PreProcessor\Chain
     */
    private function createChain(LocalInterface $asset, $dir, $path)
    {
        if ($path) {
            $origContent = $this->readFactory->create($dir)->readFile($path);
            $origContentType = $this->getContentType($path);
        } else {
            $origContent = '';
            $origContentType = $asset->getContentType();
        }

        $chain = $this->chainFactory->create(
            [
                'asset' => $asset,
                'origContent' => $origContent,
                'origContentType' => $origContentType,
                'origAssetPath' => $dir . '/' . $path
            ]
        );
        return $chain;
    }
}
