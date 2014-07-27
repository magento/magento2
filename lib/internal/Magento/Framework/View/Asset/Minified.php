<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Asset;

/**
 * Minified page asset
 */
class Minified implements MergeableInterface
{
    /**#@+
     * Strategies for verifying whether the files need to be minified
     */
    const FILE_EXISTS = 'file_exists';
    const MTIME = 'mtime';
    /**#@-*/

    /**
     * LocalInterface
     *
     * @var LocalInterface
     */
    protected $originalAsset;

    /**
     * @var string
     */
    protected $strategy;

    /**
     * File
     *
     * @var string
     */
    protected $file;

    /**
     * Relative path to the file
     *
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \Magento\Framework\View\Asset\File\Context
     */
    protected $context;

    /**
     * URL
     *
     * @var string
     */
    protected $url;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface
     */
    protected $adapter;

    /**
     * Logger
     *
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * Directory object for root directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDir;

    /**
     * Directory object for static view directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $staticViewDir;

    /**
     * Url configuration
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $baseUrl;

    /**
     * Constructor
     *
     * @param LocalInterface $asset
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\UrlInterface $baseUrl
     * @param \Magento\Framework\Code\Minifier\AdapterInterface $adapter
     * @param string $strategy
     */
    public function __construct(
        LocalInterface $asset,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\UrlInterface $baseUrl,
        \Magento\Framework\Code\Minifier\AdapterInterface $adapter,
        $strategy = self::FILE_EXISTS
    ) {
        $this->originalAsset = $asset;
        $this->strategy = $strategy;
        $this->logger = $logger;
        $this->rootDir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->staticViewDir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR);
        $this->baseUrl = $baseUrl;
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            $this->process();
        }
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->originalAsset->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        if (empty($this->file)) {
            $this->process();
        }
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (empty($this->path)) {
            $this->process();
        }
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilePath()
    {
        if (null === $this->filePath) {
            $this->process();
        }
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        if (null === $this->context) {
            $this->process();
        }
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->originalAsset->getModule();
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->path) {
            $this->process();
        }
        return $this->staticViewDir->readFile($this->path);
    }

    /**
     * Minify content of child asset
     *
     * @return void
     */
    protected function process()
    {
        if ($this->isFileMinified($this->originalAsset->getPath())) {
            $this->fillPropertiesByOriginalAsset();
        } else if ($this->hasPreminifiedFile($this->originalAsset->getSourceFile())) {
            $this->fillPropertiesByOriginalAssetWithMin();
        } else {
            try {
                $this->fillPropertiesByMinifyingAsset();
            } catch (\Exception $e) {
                $this->logger->logException(
                    new \Magento\Framework\Exception(
                        'Could not minify file: ' . $this->originalAsset->getSourceFile(),
                        0,
                        $e
                    )
                );
                $this->fillPropertiesByOriginalAsset();
            }
        }
    }

    /**
     * Check, whether file is already minified
     *
     * @param string $fileName
     * @return bool
     */
    protected function isFileMinified($fileName)
    {
        return (bool)preg_match('#.min.\w+$#', $fileName);
    }

    /**
     * Check, whether the file has its preminified version in the same directory
     *
     * @param string $fileName
     * @return bool
     */
    protected function hasPreminifiedFile($fileName)
    {
        $minifiedFile = $this->composeMinifiedName($fileName);
        return $this->rootDir->isExist($this->rootDir->getRelativePath($minifiedFile));
    }

    /**
     * Compose path to a preminified file in the same folder out of path to an original file
     *
     * @param string $fileName
     * @return string
     */
    protected function composeMinifiedName($fileName)
    {
        return preg_replace('/\\.([^.]*)$/', '.min.$1', $fileName);
    }

    /**
     * Fill the properties by bare copying properties from original asset
     *
     * @return void
     */
    protected function fillPropertiesByOriginalAsset()
    {
        $this->file = $this->originalAsset->getSourceFile();
        $this->path = $this->originalAsset->getPath();
        $this->filePath = $this->originalAsset->getFilePath();
        $this->context = $this->originalAsset->getContext();
        $this->url = $this->originalAsset->getUrl();
    }

    /**
     * Fill the properties by copying properties from original asset and adding '.min' inside them
     *
     * @return void
     */
    protected function fillPropertiesByOriginalAssetWithMin()
    {
        $this->file = $this->composeMinifiedName($this->originalAsset->getSourceFile());
        $this->path = $this->composeMinifiedName($this->originalAsset->getPath());
        $this->filePath = $this->composeMinifiedName($this->originalAsset->getFilePath());
        $this->context = $this->originalAsset->getContext();
        $this->url = $this->composeMinifiedName($this->originalAsset->getUrl());
    }

    /**
     * Generate minified file and fill the properties to reference that file
     *
     * @return void
     */
    protected function fillPropertiesByMinifyingAsset()
    {
        $path = $this->originalAsset->getPath();
        $this->context = new \Magento\Framework\View\Asset\File\Context(
            $this->baseUrl->getBaseUrl(array('_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC)),
            \Magento\Framework\App\Filesystem::STATIC_VIEW_DIR,
            \Magento\Framework\App\Filesystem\DirectoryList::CACHE_VIEW_REL_DIR . '/minified'
        );
        $this->filePath = md5($path) . '_' . $this->composeMinifiedName(basename($path));
        $this->path = $this->context->getPath() . '/' . $this->filePath;
        $this->minify();
        $this->file = $this->staticViewDir->getAbsolutePath($this->path);
        $this->url = $this->context->getBaseUrl() . $this->path;
    }

    /**
     * Perform actual minification
     *
     * @return void
     */
    private function minify()
    {
        $isExists = $this->staticViewDir->isExist($this->path);
        if (!$isExists) {
            $shouldMinify = true;
        } elseif ($this->strategy == self::FILE_EXISTS) {
            $shouldMinify = false;
        } else {
            $origlFile = $this->rootDir->getRelativePath($this->originalAsset->getSourceFile());
            $origMtime = $this->rootDir->stat($origlFile)['mtime'];
            $minMtime = $this->staticViewDir->stat($this->path)['mtime'];
            $shouldMinify = $origMtime != $minMtime;
        }
        if ($shouldMinify) {
            $content = $this->adapter->minify($this->originalAsset->getContent());
            $this->staticViewDir->writeFile($this->path, $content);
        }
    }
}
