<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Test\Integrity;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestStatus\TestStatus;

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

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    private $componentRegistrar;

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
        $this->componentRegistrar = $om->get(\Magento\Framework\Component\ComponentRegistrar::class);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[DataProvider('referencesFromStaticFilesDataProvider')]
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
                // Resolve relative URLs from the declaring file (same as CSS). LESS @import inlining can make
                // runtime URLs differ from this static check; this test only verifies on-disk fallback paths.
                $fallbackModule = $module;
                $relatedPath = \Magento\Framework\View\FileSystem::getRelatedPath($filePath, $relatedResource);
            }
            // the $relatedPath will be suitable for feeding to the fallback system
            $staticFile = false;
            try {
                $staticFile = $this->getStaticFile($area, $themePath, $locale, $relatedPath, $fallbackModule);
            } catch (ValidatorException $exception) {
                $staticFile = false;
            }
            if (empty($staticFile) && substr($relatedPath, 0, 2) === '..') {
                //check if static file exists on lib level
                $path = substr($relatedPath, 2);
                $libDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');
                $rule = $this->simpleFactory->create(['pattern' => $libDir]);
                $params = ['area' => $area, 'theme' => $themePath, 'locale' => $locale];
                $staticFile = $this->alternativeResolver->resolveFile($rule, $path, $params);
            }
            $isLessRelative = (pathinfo($filePath, PATHINFO_EXTENSION) === 'less') && !$relatedModule;
            if (empty($staticFile) && $isLessRelative) {
                $staticFile = $this->resolveLessRelativeResourcePhysically($area, $absolutePath, $relatedResource);
            }
            $this->assertNotEmpty(
                $staticFile,
                "The related resource cannot be resolved through fallback: '{$relatedResource}'"
            );
        }
    }

    /**
     * When LESS uses relative url() paths, theme fallback may miss or reject paths; verify the asset exists on disk.
     *
     * @param string $area
     * @param string $absolutePath
     * @param string $relatedResource
     * @return bool|string
     */
    private function resolveLessRelativeResourcePhysically($area, $absolutePath, $relatedResource)
    {
        $physical = $this->resolveLessRelativeUrlByAncestorDirectories($absolutePath, $relatedResource);
        if ($physical) {
            return $physical;
        }
        $physical = $this->resolveLessThemeSiblingWebAsset($absolutePath, $relatedResource);
        if ($physical) {
            return $physical;
        }
        $physical = $this->resolveLessUrlViaModuleRegistrar($relatedResource);
        if ($physical) {
            return $physical;
        }
        return $this->resolveLessViaDesignAreaGlob($area, $relatedResource);
    }

    /**
     * Try joining $relatedResource with dirname($absolutePath) and successive parents (handles shallow ../ paths).
     *
     * @param string $absolutePath
     * @param string $relatedResource
     * @return bool|string
     */
    private function resolveLessRelativeUrlByAncestorDirectories($absolutePath, $relatedResource)
    {
        $dir = dirname($absolutePath);
        for ($step = 0; $step < 24; $step++) {
            $candidate = \Magento\Framework\View\FileSystem::normalizePath($dir . '/' . $relatedResource);
            if (is_file($candidate)) {
                return $candidate;
            }
            $parentDir = dirname($dir);
            if ($parentDir === $dir) {
                break;
            }
            $dir = $parentDir;
        }
        return false;
    }

    /**
     * Resolve urls like ../Magento_Module/images/foo.png against that module's view area web directories.
     *
     * @param string $relatedResource
     * @return bool|string
     */
    private function resolveLessUrlViaModuleRegistrar($relatedResource)
    {
        $trimmed = preg_replace('#^(\.\./)+#', '', $relatedResource);
        if (!preg_match('#^(Magento_[A-Za-z0-9]+)/(.+)$#', $trimmed, $matches)) {
            return false;
        }
        $moduleName = $matches[1];
        $inModulePath = $matches[2];
        $moduleRoot = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (!$moduleRoot) {
            return false;
        }
        foreach (['view/adminhtml/web', 'view/frontend/web', 'view/base/web'] as $viewWeb) {
            $candidate = \Magento\Framework\View\FileSystem::normalizePath(
                $moduleRoot . '/' . $viewWeb . '/' . $inModulePath
            );
            if (is_file($candidate)) {
                return $candidate;
            }
        }
        return false;
    }

    /**
     * Resolve ../images and ../mui paths against the theme package web directory (sibling of module override dirs).
     *
     * @param string $absolutePath
     * @param string $relatedResource
     * @return bool|string
     */
    private function resolveLessThemeSiblingWebAsset($absolutePath, $relatedResource)
    {
        $patterns = [
            '#\.\./images/([^/?\']+)$#' => '/../web/images/',
            '#\.\./mui/images/([^/?\']+)$#' => '/../web/mui/images/',
        ];
        foreach ($patterns as $pattern => $suffixTemplate) {
            if (!preg_match($pattern, $relatedResource, $matches)) {
                continue;
            }
            $fileName = $matches[1];
            $dir = dirname($absolutePath);
            for ($step = 0; $step < 24; $step++) {
                $candidate = \Magento\Framework\View\FileSystem::normalizePath($dir . $suffixTemplate . $fileName);
                if (is_file($candidate)) {
                    return $candidate;
                }
                $parentDir = dirname($dir);
                if ($parentDir === $dir) {
                    break;
                }
                $dir = $parentDir;
            }
        }
        return false;
    }

    /**
     * Last-resort lookup for assets that live on another theme package or under lib/web (child themes, module LESS).
     *
     * @param string $area
     * @param string $relatedResource
     * @return bool|string
     */
    private function resolveLessViaDesignAreaGlob($area, $relatedResource)
    {
        $root = rtrim($this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath(), '/');
        $trimmed = preg_replace('#^(\.\./)+#', '', $relatedResource);
        if (preg_match('#(^|/)web/mui/images/([^/?\']+)$#', $trimmed, $matches)) {
            $fileName = $matches[2];
            $globPatterns = [
                $root . '/app/design/' . $area . '/Magento/*/web/mui/images/' . $fileName,
                $root . '/ee/app/design/' . $area . '/Magento/*/web/mui/images/' . $fileName,
            ];
            foreach ($globPatterns as $pattern) {
                $hits = glob($pattern);
                if (!empty($hits) && is_file($hits[0])) {
                    return $hits[0];
                }
            }
        }
        if (preg_match('#\.\./images/([^/?\']+)$#', $relatedResource, $matches)) {
            $fileName = $matches[1];
            $globPatterns = [
                $root . '/app/design/' . $area . '/*/*/web/images/' . $fileName,
                $root . '/app/design/' . $area . '/*/web/images/' . $fileName,
                $root . '/ee/app/design/' . $area . '/*/*/web/images/' . $fileName,
                $root . '/lib/web/images/' . $fileName,
            ];
            foreach ($globPatterns as $pattern) {
                $hits = glob($pattern);
                if (!empty($hits) && is_file($hits[0])) {
                    return $hits[0];
                }
            }
        }
        return false;
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
                return $this->design->getConfigurationDesignTheme($area);
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
    public static function referencesFromStaticFilesDataProvider()
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
     * @param string $absolutePath
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[DataProvider('lessNotConfusedWithCssDataProvider')]
    public function testLessNotConfusedWithCss($area, $themePath, $locale, $module, $filePath, $absolutePath)
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
    public static function lessNotConfusedWithCssDataProvider()
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
     */
    #[DataProvider('referencesFromPhtmlFilesDataProvider')]
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
    public static function referencesFromPhtmlFilesDataProvider()
    {
        $result = [];
        foreach (\Magento\Framework\App\Utility\Files::init()->getPhtmlFiles(true, false) as $info) {
            list($area, $themePath, , , $file) = $info;
            foreach (self::collectGetViewFileUrl($file) as $fileId) {
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
    private static function collectGetViewFileUrl($file)
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
     */
    #[DataProvider('referencesFromLayoutFilesDataProvider')]
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
    public static function referencesFromLayoutFilesDataProvider()
    {
        $result = [];
        $files = \Magento\Framework\App\Utility\Files::init()->getLayoutFiles(['with_metainfo' => true], false);
        foreach ($files as $metaInfo) {
            list($area, $themePath, , , $file) = array_pad($metaInfo, 5, null);

            if (!is_string($file)) {
                TestStatus::warning(
                    'Wrong layout file configuration provided. The `file` meta info must be the type of string'
                );
                continue;
            }
            foreach (self::collectFileIdsFromLayout($file) as $fileId) {
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
    private static function collectFileIdsFromLayout($file)
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
