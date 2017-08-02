<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package\Bundle;

use Magento\Deploy\Config\BundleConfig;
use Magento\Deploy\Package\BundleInterface;
use Magento\Framework\Filesystem;
use \Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Minification;

/**
 * RequireJs static files bundle object
 *
 * All files added will be bundled to multiple bundle files compatible with RequireJS AMD format
 * @since 2.2.0
 */
class RequireJs implements BundleInterface
{
    /**
     * Static files Bundling configuration class
     *
     * @var BundleConfig
     * @since 2.2.0
     */
    private $bundleConfig;

    /**
     * Helper class for static files minification related processes
     *
     * @var Minification
     * @since 2.2.0
     */
    private $minification;

    /**
     * Static content directory writable interface
     *
     * @var WriteInterface
     * @since 2.2.0
     */
    private $staticDir;

    /**
     * Package area
     *
     * @var string
     * @since 2.2.0
     */
    private $area;

    /**
     * Package theme
     *
     * @var string
     * @since 2.2.0
     */
    private $theme;

    /**
     * Package locale
     *
     * @var string
     * @since 2.2.0
     */
    private $locale;

    /**
     * Bundle content pools
     *
     * @var string[]
     * @since 2.2.0
     */
    private $contentPools = [
        'js' => 'jsbuild',
        'html' => 'text'
    ];

    /**
     * Files to be bundled
     *
     * @var array[]
     * @since 2.2.0
     */
    private $files = [
        'jsbuild' => [],
        'text' => []
    ];

    /**
     * Files content cache
     *
     * @var string[]
     * @since 2.2.0
     */
    private $fileContent = [];

    /**
     * Incremental index of bundle file
     *
     * Chosen bundling strategy may result in creating multiple bundle files instead of one
     *
     * @var int
     * @since 2.2.0
     */
    private $bundleFileIndex = 0;

    /**
     * Relative path to directory where bundle files should be created
     *
     * @var string
     * @since 2.2.0
     */
    private $pathToBundleDir;

    /**
     * Bundle constructor
     *
     * @param Filesystem $filesystem
     * @param BundleConfig $bundleConfig
     * @param Minification $minification
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @param array $contentPools
     * @since 2.2.0
     */
    public function __construct(
        Filesystem $filesystem,
        BundleConfig $bundleConfig,
        Minification $minification,
        $area,
        $theme,
        $locale,
        array $contentPools = []
    ) {
        $this->filesystem = $filesystem;
        $this->bundleConfig = $bundleConfig;
        $this->minification = $minification;
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->area = $area;
        $this->theme = $theme;
        $this->locale = $locale;
        $this->contentPools = array_merge($this->contentPools, $contentPools);
        $this->pathToBundleDir = $this->area . '/' . $this->theme . '/' . $this->locale . '/' . self::BUNDLE_JS_DIR;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function addFile($filePath, $sourcePath, $contentType)
    {
        // all unknown content types designated to "text" pool
        $contentPoolName = isset($this->contentPools[$contentType]) ? $this->contentPools[$contentType] : 'text';
        $this->files[$contentPoolName][$filePath] = $sourcePath;
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function flush()
    {
        $this->bundleFileIndex = 0;

        $bundleFile = null;
        foreach ($this->files as $contentPoolName => $files) {
            if (empty($files)) {
                continue;
            }
            $content = [];
            $freeSpace = $this->getBundleFileMaxSize();
            $bundleFile = $this->startNewBundleFile($contentPoolName);
            foreach ($files as $filePath => $sourcePath) {
                $fileContent = $this->getFileContent($sourcePath);
                $size = mb_strlen($fileContent, 'utf-8') / 1024;
                if ($freeSpace > $size) {
                    $freeSpace -= $size;
                    $content[$this->minification->addMinifiedSign($filePath)] = $fileContent;
                } else {
                    $this->endBundleFile($bundleFile, $content);
                    $freeSpace = $this->getBundleFileMaxSize();
                    $freeSpace -= $size;
                    $content = [
                        $this->minification->addMinifiedSign($filePath) => $fileContent
                    ];
                    $bundleFile = $this->startNewBundleFile($contentPoolName);
                }
            }
            $this->endBundleFile($bundleFile, $content);
        }

        if ($bundleFile) {
            $bundleFile->write($this->getInitJs());
        }

        $this->files = [];
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function clear()
    {
        $this->staticDir->delete($this->pathToBundleDir);
    }

    /**
     * Create new bundle file and write beginning content to it
     *
     * @param string $contentPoolName
     * @return WriteInterface
     * @since 2.2.0
     */
    private function startNewBundleFile($contentPoolName)
    {
        $bundleFile = $this->staticDir->openFile(
            $this->minification->addMinifiedSign($this->pathToBundleDir . '/bundle' . $this->bundleFileIndex . '.js')
        );
        $bundleFile->write("require.config({\"config\": {\n");
        $bundleFile->write("        \"{$contentPoolName}\":");
        ++$this->bundleFileIndex;
        return $bundleFile;
    }

    /**
     * Write ending content to bundle file
     *
     * @param WriteInterface $bundleFile
     * @param array $contents
     * @return bool true on success
     * @since 2.2.0
     */
    private function endBundleFile(WriteInterface $bundleFile, array $contents)
    {
        if ($contents) {
            $content = json_encode($contents, JSON_UNESCAPED_SLASHES);
            $bundleFile->write("{$content}\n");
        } else {
            $bundleFile->write("{}\n");
        }
        $bundleFile->write("}});\n");
        return true;
    }

    /**
     * Get content of static file
     *
     * @param string $sourcePath
     * @return string
     * @since 2.2.0
     */
    private function getFileContent($sourcePath)
    {
        if (!isset($this->fileContent[$sourcePath])) {
            $this->fileContent[$sourcePath] = utf8_encode(
                $this->staticDir->readFile($this->minification->addMinifiedSign($sourcePath))
            );
        }
        return $this->fileContent[$sourcePath];
    }

    /**
     * Get max size of bundle files (in KB)
     *
     * @return int
     * @since 2.2.0
     */
    private function getBundleFileMaxSize()
    {
        return $this->bundleConfig->getBundleFileMaxSize($this->area, $this->theme);
    }

    /**
     * Bundle initialization script content (this must be added to the latest bundle file at the very end)
     *
     * @return string
     * @since 2.2.0
     */
    private function getInitJs()
    {
        return "require.config({\n" .
        "    bundles: {\n" .
        "        'mage/requirejs/static': [\n" .
        "            'jsbuild',\n" .
        "            'buildTools',\n" .
        "            'text',\n" .
        "            'statistician'\n" .
        "        ]\n" .
        "    },\n" .
        "    deps: [\n" .
        "        'jsbuild'\n" .
        "    ]\n" .
        "});\n";
    }
}
