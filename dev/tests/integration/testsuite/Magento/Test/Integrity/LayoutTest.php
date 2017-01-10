<?php
/**
 * Layout nodes integrity tests
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Test\Integrity;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cached lists of files
     *
     * @var array
     */
    protected static $_cachedFiles = [];

    public static function setUpBeforeClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            ['preferences' => [\Magento\Theme\Model\Theme::class => \Magento\Theme\Model\Theme\Data::class]]
        );
    }

    public static function tearDownAfterClass()
    {
        self::$_cachedFiles = []; // Free memory
    }

    /**
     * Composes full layout xml for designated parameters
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function _composeXml(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        /** @var \Magento\Framework\View\Layout\ProcessorInterface $layoutUpdate */
        $layoutUpdate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout\ProcessorInterface::class,
            ['theme' => $theme]
        );
        return $layoutUpdate->getFileLayoutUpdatesXml();
    }

    /**
     * Validate node's declared position in hierarchy and add errors to the specified array if found
     *
     * @param \SimpleXMLElement $node
     * @param \Magento\Framework\View\Layout\Element $xml
     * @param array &$errors
     */
    protected function _collectHierarchyErrors($node, $xml, &$errors)
    {
        $name = $node->getName();
        $refName = $node->getAttribute('type') == $node->getAttribute('parent');
        if ($refName) {
            $refNode = $xml->xpath("/layouts/{$refName}");
            if (!$refNode) {
                $errors[$name][] = "Node '{$refName}', referenced in hierarchy, does not exist";
            }
        }
    }

    /**
     * List all themes available in the system
     *
     * A test that uses such data provider is supposed to gather view resources in provided scope
     * and analyze their integrity. For example, merge and verify all layouts in this scope.
     *
     * Such tests allow to uncover complicated code integrity issues, that may emerge due to view fallback mechanism.
     * For example, a module layout file is overlapped by theme layout, which has mistakes.
     * Such mistakes can be uncovered only when to emulate this particular theme.
     * Also emulating "no theme" mode allows to detect inversed errors: when there is a view file with mistake
     * in a module, but it is overlapped by every single theme by files without mistake. Putting question of code
     * duplication aside, it is even more important to detect such errors, than an error in a single theme.
     *
     * @return array
     */
    public function areasAndThemesDataProvider()
    {
        $result = [];
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Design\ThemeInterface::class
        )->getCollection();
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            $result[$theme->getFullPath() . ' [' . $theme->getId() . ']'] = [$theme];
        }
        return $result;
    }

    public function testHandleLabels()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param \Magento\Framework\View\Design\ThemeInterface $theme
             */
            function (\Magento\Framework\View\Design\ThemeInterface $theme) {
                $xml = $this->_composeXml($theme);

                $xpath = '/layouts/*[@design_abstraction]';
                $handles = $xml->xpath($xpath) ?: [];

                /** @var \Magento\Framework\View\Layout\Element $node */
                $errors = [];
                foreach ($handles as $node) {
                    if (!$node->xpath('@label')) {
                        $nodeId = $node->getAttribute('id') ? ' id=' . $node->getAttribute('id') : '';
                        $errors[] = $node->getName() . $nodeId;
                    }
                }
                if ($errors) {
                    $this->fail(
                        'The following handles must have label, but they don\'t have it:' . PHP_EOL . var_export(
                            $errors,
                            true
                        )
                    );
                }
            },
            $this->areasAndThemesDataProvider()
        );
    }

    public function testPageTypesDeclaration()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check whether page types are declared only in layout update files allowed for it - base ones
             */
            function (\Magento\Framework\View\File $layout) {
                $content = simplexml_load_file($layout->getFilename());
                $this->assertEmpty(
                    $content->xpath(\Magento\Framework\View\Model\Layout\Merge::XPATH_HANDLE_DECLARATION),
                    "Theme layout update '" . $layout->getFilename() . "' contains page type declaration(s)"
                );
            },
            $this->pageTypesDeclarationDataProvider()
        );
    }

    /**
     * Get theme layout updates
     *
     * @return \Magento\Framework\View\File[]
     */
    public function pageTypesDeclarationDataProvider()
    {
        /** @var $themeUpdates \Magento\Framework\View\File\Collector\ThemeModular */
        $themeUpdates = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\View\File\Collector\ThemeModular::class, ['subDir' => 'layout']);
        /** @var $themeUpdatesOverride \Magento\Framework\View\File\Collector\Override\ThemeModular */
        $themeUpdatesOverride = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Framework\View\File\Collector\Override\ThemeModular::class,
                ['subDir' => 'layout/override/theme']
            );
        /** @var $themeCollection \Magento\Theme\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Theme\Collection::class
        );
        /** @var $themeLayouts \Magento\Framework\View\File[] */
        $themeLayouts = [];
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            $themeLayouts = array_merge($themeLayouts, $themeUpdates->getFiles($theme, '*.xml'));
            $themeLayouts = array_merge($themeLayouts, $themeUpdatesOverride->getFiles($theme, '*.xml'));
        }
        $result = [];
        foreach ($themeLayouts as $layout) {
            $result[$layout->getFileIdentifier()] = [$layout];
        }
        return $result;
    }

    public function testOverrideBaseFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check, that for an overriding file ($themeFile) in a theme ($theme), there is a corresponding base file
             *
             * @param \Magento\Framework\View\File $themeFile
             * @param \Magento\Framework\View\Design\ThemeInterface $theme
             */
            function ($themeFile, $theme) {
                $baseFiles = self::_getCachedFiles(
                    $theme->getArea(), \Magento\Framework\View\File\Collector\Base::class,
                    $theme
                );
                $fileKey = $themeFile->getModule() . '/' . $themeFile->getName();
                $this->assertArrayHasKey(
                    $fileKey,
                    $baseFiles,
                    sprintf("Could not find base file, overridden by theme file '%s'.", $themeFile->getFilename())
                );
            },
            $this->overrideBaseFilesDataProvider()
        );
    }

    public function testOverrideThemeFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check, that for an ancestor-overriding file ($themeFile) in a theme ($theme),
             * there is a corresponding file in that ancestor theme
             *
             * @param \Magento\Framework\View\File $themeFile
             * @param \Magento\Framework\View\Design\ThemeInterface $theme
             */
            function ($themeFile, $theme) {
                // Find an ancestor theme, where a file is to be overridden
                $ancestorTheme = $theme;
                while ($ancestorTheme = $ancestorTheme->getParentTheme()) {
                    if ($ancestorTheme == $themeFile->getTheme()) {
                        break;
                    }
                }
                $this->assertNotNull(
                    $ancestorTheme,
                    sprintf(
                        'Could not find ancestor theme "%s", ' .
                        'its layout file is supposed to be overridden by file "%s".',
                        $themeFile->getTheme()->getCode(),
                        $themeFile->getFilename()
                    )
                );

                // Search for the overridden file in the ancestor theme
                $ancestorFiles = self::_getCachedFiles($ancestorTheme->getFullPath(), \Magento\Framework\View\File\Collector\ThemeModular::class, $ancestorTheme);
                $fileKey = $themeFile->getModule() . '/' . $themeFile->getName();
                $this->assertArrayHasKey(
                    $fileKey,
                    $ancestorFiles,
                    sprintf(
                        "Could not find original file in '%s' theme, overridden by file '%s'.",
                        $themeFile->getTheme()->getCode(),
                        $themeFile->getFilename()
                    )
                );
            },
            $this->overrideThemeFilesDataProvider()
        );
    }

    /**
     * Retrieve list of cached source files
     *
     * @param string $cacheKey
     * @param string $sourceClass
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\File[]
     */
    protected static function _getCachedFiles(
        $cacheKey,
        $sourceClass,
        \Magento\Framework\View\Design\ThemeInterface $theme
    ) {
        if (!isset(self::$_cachedFiles[$cacheKey])) {
            /* @var $fileList \Magento\Framework\View\File[] */
            $fileList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create($sourceClass, ['subDir' => 'layout'])->getFiles($theme, '*.xml');
            $files = [];
            foreach ($fileList as $file) {
                $files[$file->getModule() . '/' . $file->getName()] = true;
            }
            self::$_cachedFiles[$cacheKey] = $files;
        }
        return self::$_cachedFiles[$cacheKey];
    }

    /**
     * @return array
     */
    public function overrideBaseFilesDataProvider()
    {
        return $this->_retrieveFilesForEveryTheme(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create(
                    \Magento\Framework\View\File\Collector\Override\Base::class,
                    ['subDir' => 'layout/override/base']
                )
        );
    }

    /**
     * @return array
     */
    public function overrideThemeFilesDataProvider()
    {
        return $this->_retrieveFilesForEveryTheme(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create(
                    \Magento\Framework\View\File\Collector\Override\ThemeModular::class,
                    ['subDir' => 'layout/override/theme']
                )
        );
    }

    /**
     * Scan all the themes in the system, for each theme retrieve list of files via $filesRetriever,
     * and return them as array of pairs [file, theme].
     *
     * @param \Magento\Framework\View\File\CollectorInterface $filesRetriever
     * @return array
     */
    protected function _retrieveFilesForEveryTheme(\Magento\Framework\View\File\CollectorInterface $filesRetriever)
    {
        $result = [];
        /** @var $themeCollection \Magento\Theme\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Theme\Collection::class
        );
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            foreach ($filesRetriever->getFiles($theme, '*.xml') as $file) {
                $result['theme: ' . $theme->getFullPath() . ', ' . $file->getFilename()] = [$file, $theme];
            }
        }
        return $result;
    }
}
