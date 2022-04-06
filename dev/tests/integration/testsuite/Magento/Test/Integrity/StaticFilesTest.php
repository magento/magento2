<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * An integrity test that searches for references to static files and asserts that they are resolved via fallback
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile
     */
    private $fallback;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     */
    private $explicitFallback;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    private $themeRepo;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    private $baseTheme;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Alternative
     */
    private $alternativeResolver;

    /**
     * Factory for simple rule
     *
     * @var \Magento\Framework\View\Design\Fallback\Rule\SimpleFactory
     */
    private $simpleFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager();
        $this->fallback = $om->get(\Magento\Framework\View\Design\FileResolution\Fallback\StaticFile::class);
        $this->explicitFallback = $om->get(
            \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple::class
        );
        $this->themeRepo = $om->get(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
        $this->design = $om->get(\Magento\Framework\View\DesignInterface::class);
        $this->baseTheme = $om->get(\Magento\Framework\View\Design\ThemeInterface::class);
        $this->alternativeResolver = $om->get(
            \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Alternative::class
        );
        $this->simpleFactory = $om->get(\Magento\Framework\View\Design\Fallback\Rule\SimpleFactory::class);
        $this->filesystem = $om->get(\Magento\Framework\Filesystem::class);
    }

    /**
     * Scan references to files from other static files and assert they are correct
     *
     * The CSS or LESS files may refer to other resources using `import` or url() notation
     * We want to check integrity of all these references
     * Note that the references may have syntax specific to the Magento preprocessing subsystem
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string $filePath
     * @param string $absolutePath
     * @dataProvider referencesFromStaticFilesDataProvider
     */
    public function testReferencesFromStaticFiles($area, $themePath, $locale, $module, $filePath, $absolutePath)
    {
        $contents = file_get_contents($absolutePath);
        preg_match_all(
            \Magento\Framework\View\Url\CssResolver::REGEX_CSS_RELATIVE_URLS,
            $contents,
            $matches
        );
        foreach ($matches[1] as $relatedResource) {
            if (false !== strpos($relatedResource, '@')) { // unable to parse paths with LESS variables/mixins
                continue;
            }
            list($relatedModule, $relatedPath) =
                \Magento\Framework\View\Asset\Repository::extractModule($relatedResource);
            if ($relatedModule) {
                $fallbackModule = $relatedModule;
            } else {
                if ('less' == pathinfo($filePath, PATHINFO_EXTENSION)) {
                    /**
                     * The LESS library treats the related resources with relative links not in the same way as CSS:
                     * when another LESS file is included, it is embedded directly into the resulting document, but the
                     * relative paths of related resources are not adjusted accordingly to the new root file.
                     * Probably it is a bug of the LESS library.
                     */
                    $this->markTestSkipped("Due to LESS library specifics, the '{$relatedResource}' cannot be tested.");
                }
                $fallbackModule = $module;
                $relatedPath = \Magento\Framework\View\FileSystem::getRelatedPath($filePath, $relatedResource);
            }
            // the $relatedPath will be suitable for feeding to the fallback system
            $staticFile = $this->getStaticFile($area, $themePath, $locale, $relatedPath, $fallbackModule);
            if (empty($staticFile) && substr($relatedPath, 0, 2) === '..') {
                //check if static file exists on lib level
                $path = substr($relatedPath, 2);
                $libDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');
                $rule = $this->simpleFactory->create(['pattern' => $libDir]);
                $params = ['area' => $area, 'theme' => $themePath, 'locale' => $locale];
                $staticFile = $this->alternativeResolver->resolveFile($rule, $path, $params);
            }
            $this->assertNotEmpty(
                $staticFile,
                "The related resource cannot be resolved through fallback: '{$relatedResource}'"
            );
        }
    }

    /**
     * Get a default theme path for specified area
     *
     * @param string $area
     * @return string
     * @throws \LogicException
     */
    private function getDefaultThemePath($area)
    {
        switch ($area) {
            case 'frontend':
                return $this->design->getConfigurationDesignTheme($area);
            case 'adminhtml':
                return 'Magento/backend';
            case 'doc':
                return 'Magento/blank';
            default:
                throw new \LogicException('Unable to determine theme path');
        }
    }

    /**
     * Get static file through fallback system using specified params
     *
     * @param string $area
     * @param string|\Magento\Framework\View\Design\ThemeInterface $theme - either theme path (string) or theme object
     * @param string $locale
     * @param string $filePath
     * @param string $module
     * @param bool $isExplicit
     * @return bool|string
     */
    private function getStaticFile($area, $theme, $locale, $filePath, $module = null, $isExplicit = false)
    {
        if ($area == 'base') {
            $theme = $this->baseTheme;
        }
        if (!is_object($theme)) {
            $themePath = $theme ?: $this->getDefaultThemePath($area);
            $theme = $this->themeRepo->create($themePath, $area);
        }
        if ($isExplicit) {
            $type = \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE;
            return $this->explicitFallback->resolve($type, $filePath, $area, $theme, $locale, $module);
        }
        return $this->fallback->getFile($area, $theme, $locale, $filePath, $module);
    }

    /**
     * @return array
     */
    public function referencesFromStaticFilesDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getStaticPreProcessingFiles('*.{less,css}');
    }

    /**
     * There must be either .css or .less file, because if there are both, then .less will not be found by fallback
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string $filePath
     * @dataProvider lessNotConfusedWithCssDataProvider
     */
    public function testLessNotConfusedWithCss($area, $themePath, $locale, $module, $filePath)
    {
        if (false !== strpos($filePath, 'widgets.css')) {
            $filePath .= '';
        }
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $dirName = dirname($filePath);
        if ('.' == $dirName) {
            $dirName = '';
        } else {
            $dirName .= '/';
        }
        $cssPath = $dirName . $fileName . '.css';
        $lessPath = $dirName . $fileName . '.less';
        $cssFile = $this->getStaticFile($area, $themePath, $locale, $cssPath, $module, true);
        $lessFile = $this->getStaticFile($area, $themePath, $locale, $lessPath, $module, true);
        $this->assertFalse(
            $cssFile && $lessFile,
            "A resource file of only one type must exist. Both found: '$cssFile' and '$lessFile'"
        );
    }

    /**
     * @return array
     */
    public function lessNotConfusedWithCssDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getStaticPreProcessingFiles('*.{less,css}');
    }

    /**
     * Test if references $this->getViewFileUrl() in .phtml-files are correct
     *
     * @param string $phtmlFile
     * @param string $area
     * @param string $themePath
     * @param string $fileId
     * @dataProvider referencesFromPhtmlFilesDataProvider
     */
    public function testReferencesFromPhtmlFiles($phtmlFile, $area, $themePath, $fileId)
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule($fileId);
        $this->assertNotEmpty(
            $this->getStaticFile($area, $themePath, 'en_US', $filePath, $module),
            "Unable to locate '{$fileId}' reference from {$phtmlFile}"
        );
    }

    /**
     * @return array
     */
    public function referencesFromPhtmlFilesDataProvider()
    {
        $result = [];
        foreach (\Magento\Framework\App\Utility\Files::init()->getPhtmlFiles(true, false) as $info) {
            list($area, $themePath, , , $file) = $info;
            foreach ($this->collectGetViewFileUrl($file) as $fileId) {
                $result[] = [$file, $area, $themePath, $fileId];
            }
        }
        return $result;
    }

    /**
     * Find invocations of $block->getViewFileUrl() and extract the first argument value
     *
     * @param string $file
     * @return array
     */
    private function collectGetViewFileUrl($file)
    {
        $result = [];
        if (preg_match_all('/\$block->getViewFileUrl\(\'([^\']+?)\'\)/', file_get_contents($file), $matches)) {
            foreach ($matches[1] as $fileId) {
                $result[] = $fileId;
            }
        }
        return $result;
    }

    /**
     * @param string $layoutFile
     * @param string $area
     * @param string $themePath
     * @param string $fileId
     * @dataProvider referencesFromLayoutFilesDataProvider
     */
    public function testReferencesFromLayoutFiles($layoutFile, $area, $themePath, $fileId)
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule($fileId);
        $this->assertNotEmpty(
            $this->getStaticFile($area, $themePath, 'en_US', $filePath, $module),
            "Unable to locate '{$fileId}' reference from layout XML in {$layoutFile}"
        );
    }

    /**
     * @return array
     */
    public function referencesFromLayoutFilesDataProvider()
    {
        $result = [];
        $files = \Magento\Framework\App\Utility\Files::init()->getLayoutFiles(['with_metainfo' => true], false);
        foreach ($files as $metaInfo) {
            list($area, $themePath, , , $file) = array_pad($metaInfo, 5, null);

            if (!is_string($file)) {
                $this->addWarning(
                    'Wrong layout file configuration provided. The `file` meta info must be the type of string'
                );
                continue;
            }
            foreach ($this->collectFileIdsFromLayout($file) as $fileId) {
                $result[] = [$file, $area, $themePath, $fileId];
            }
        }
        return $result;
    }

    /**
     * Collect view file declarations in layout XML-files
     *
     * @param string $file
     * @return array
     */
    private function collectFileIdsFromLayout($file)
    {
        $xml = simplexml_load_file($file);
        $elements = $xml->xpath('//head/css|link|script');
        $result = [];
        if ($elements) {
            foreach ($elements as $node) {
                $result[] = (string)$node;
            }
        }
        return $result;
    }
}
