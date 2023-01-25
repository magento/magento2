<?php
/**
 * Test constructions of layout files
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * This test finds usages of modular view files, searched in non-modular context - it is obsolete and buggy
 * functionality, initially introduced in Magento 2.
 *
 * The test goes through modular calls of view files, and finds out, whether there are theme non-modular files
 * with the same path. Before fixing the bug, such call return theme files instead of  modular files, which is
 * incorrect. After fixing the bug, such calls will start returning modular files, which is not a file we got used
 * to see, so such cases are probably should be fixed. The test finds such suspicious places.
 *
 * The test is intended to be deleted before Magento 2 release. With the release, having non-modular files with the
 * same paths as modular ones, is legitimate.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewFileReferenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\RuleInterface
     */
    protected static $_fallbackRule;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile
     */
    protected static $_viewFilesFallback;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\File
     */
    protected static $_filesFallback;

    /**
     * @var array
     */
    protected static $_checkThemeLocales = [];

    /**
     * @var \Magento\Theme\Model\Theme\Collection
     */
    protected static $_themeCollection;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected static $_componentRegistrar;

    public static function setUpBeforeClass(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(
            ['preferences' => [\Magento\Theme\Model\Theme::class => \Magento\Theme\Model\Theme\Data::class]]
        );

        self::$_componentRegistrar = $objectManager->get(\Magento\Framework\Component\ComponentRegistrar::class);

        /** @var $fallbackPool \Magento\Framework\View\Design\Fallback\RulePool */
        $fallbackPool = $objectManager->get(\Magento\Framework\View\Design\Fallback\RulePool::class);
        self::$_fallbackRule = $fallbackPool->getRule(
            $fallbackPool::TYPE_STATIC_FILE
        );

        self::$_viewFilesFallback = $objectManager->get(
            \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile::class
        );
        self::$_filesFallback = $objectManager->get(\Magento\Framework\View\Design\FileResolution\Fallback\File::class);

        // Themes to be checked
        self::$_themeCollection = $objectManager->get(\Magento\Theme\Model\Theme\Collection::class);

        // Compose list of locales, needed to be checked for themes
        self::$_checkThemeLocales = [];
        foreach (self::$_themeCollection as $theme) {
            $themeLocales = self::_getThemeLocales($theme);
            $themeLocales[] = null;
            // Default non-localized file will need to be checked as well
            self::$_checkThemeLocales[$theme->getFullPath()] = $themeLocales;
        }
    }

    /**
     * Return array of locales, supported by the theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return array
     */
    protected static function _getThemeLocales(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $result = [];
        $patternDir = self::_getLocalePatternDir($theme);
        foreach (\ResourceBundle::getLocales('') as $locale) {
            $dir = str_replace('<locale_placeholder>', $locale, $patternDir);
            if (is_dir($dir)) {
                $result[] = $locale;
            }
        }
        return $result;
    }

    /**
     * Return pattern for theme locale directories, where <locale_placeholder> is placed to mark a locale's location.
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string
     * @throws \Exception
     */
    protected static function _getLocalePatternDir(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $localePlaceholder = '<locale_placeholder>';
        $params = ['area' => $theme->getArea(), 'theme' => $theme, 'locale' => $localePlaceholder];
        $patternDirs = self::$_fallbackRule->getPatternDirs($params);
        $themePath =  self::$_componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::THEME,
            $theme->getFullPath()
        );
        foreach ($patternDirs as $patternDir) {
            $patternPath = $patternDir . '/';
            if ((strpos($patternPath, $themePath) !== false) // It is theme's directory
                && (strpos($patternPath, $localePlaceholder) !== false) // It is localized directory
            ) {
                return $patternDir;
            }
        }
        throw new \Exception('Unable to determine theme locale path');
    }

    /**
     * @param string $modularCall
     * @param array $usages
     * @param null|string $area
     * @dataProvider modularFallbackDataProvider
     */
    public function testModularFallback($modularCall, array $usages, $area)
    {
        list(, $file) = explode(\Magento\Framework\View\Asset\Repository::FILE_ID_SEPARATOR, $modularCall);

        $wrongResolutions = [];
        foreach (self::$_themeCollection as $theme) {
            if ($area && $theme->getArea() != $area) {
                continue;
            }

            $found = $this->_getFileResolutions($theme, $file);
            $wrongResolutions = array_merge($wrongResolutions, $found);
        }

        if ($wrongResolutions) {
            // If file is found, then old functionality (find modular files in non-modular locations) is used
            $message = sprintf(
                "Found modular call:\n  %s in\n  %s\n  which may resolve to non-modular location(s):\n  %s",
                $modularCall,
                implode(', ', $usages),
                implode(', ', $wrongResolutions)
            );
            $this->fail($message);
        }
    }

    /**
     * Resolves file to find its fallback'ed paths
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $file
     * @return array
     */
    protected function _getFileResolutions(\Magento\Framework\View\Design\ThemeInterface $theme, $file)
    {
        $found = [];
        $fileResolved = self::$_filesFallback->getFile($theme->getArea(), $theme, $file);
        if (file_exists($fileResolved)) {
            $found[$fileResolved] = $fileResolved;
        }

        foreach (self::$_checkThemeLocales[$theme->getFullPath()] as $locale) {
            $fileResolved = self::$_viewFilesFallback->getFile($theme->getArea(), $theme, $locale, $file);
            if (file_exists($fileResolved)) {
                $found[$fileResolved] = $fileResolved;
            }
        }
        return $found;
    }

    /**
     * @return array
     */
    public static function modularFallbackDataProvider()
    {
        $result = [];
        foreach (self::_getFilesToProcess() as $file) {
            $file = (string)$file;

            $modulePattern = '[A-Z][a-z]+_[A-Z][a-z]+';
            $filePattern = '[[:alnum:]_/-]+\\.[[:alnum:]_./-]+';
            $pattern = '#' . $modulePattern
                . preg_quote(\Magento\Framework\View\Asset\Repository::FILE_ID_SEPARATOR)
                . $filePattern . '#S';
            if (!preg_match_all($pattern, file_get_contents($file), $matches)) {
                continue;
            }

            $area = self::_getArea($file);

            foreach ($matches[0] as $modularCall) {
                $dataSetKey = $modularCall . ' @ ' . ($area ?: 'any area');

                if (!isset($result[$dataSetKey])) {
                    $result[$dataSetKey] = ['modularCall' => $modularCall, 'usages' => [], 'area' => $area];
                }
                $result[$dataSetKey]['usages'][$file] = $file;
            }
        }
        return $result;
    }

    /**
     * Return list of files, that must be processed, searching for modular calls to view files
     *
     * @return array
     */
    protected static function _getFilesToProcess()
    {
        $result = [];
        $componentRegistrar = new \Magento\Framework\Component\ComponentRegistrar();
        $dirs = array_merge(
            $componentRegistrar->getPaths(ComponentRegistrar::MODULE),
            $componentRegistrar->getPaths(ComponentRegistrar::THEME)
        );
        foreach ($dirs as $dir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            $result = array_merge($result, iterator_to_array($iterator));
        }

        return $result;
    }

    /**
     * Get the area, where file is located.
     *
     * Null is returned, if the file is not within an area, e.g. it is a model/block/helper php-file.
     *
     * @param string $file
     * @return string|null
     */
    protected static function _getArea($file)
    {
        $file = str_replace('\\', '/', $file);
        $areaPatterns = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
            $areaPatterns[] = '#' . $themeDir . '/([^/]+)/#S';
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $areaPatterns[] = '#' . $moduleDir . '/view/([^/]+)/#S';
        }
        foreach ($areaPatterns as $pattern) {
            if (preg_match($pattern, $file, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
