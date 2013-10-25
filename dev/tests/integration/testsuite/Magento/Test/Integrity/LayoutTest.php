<?php
/**
 * Layout nodes integrity tests
 *
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
 * @category    tests
 * @package     integration
 * @subpackage  integrity
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cached lists of files
     *
     * @var array
     */
    protected static $_cachedFiles = array();

    public static function tearDownAfterClass()
    {
        self::$_cachedFiles = array(); // Free memory
    }

    public function testHandlesHierarchy()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param \Magento\View\Design\ThemeInterface $theme
             */
            function (\Magento\View\Design\ThemeInterface $theme) {
                $xml = $this->_composeXml($theme);

                /**
                 * There could be used an xpath "/layouts/*[@type or @owner or @parent]", but it randomly produced bugs,
                 * by selecting all nodes in depth. Thus it was refactored into manual nodes extraction.
                 */
                $handles = array();
                foreach ($xml->children() as $handleNode) {
                    if ($handleNode->getAttribute('type')
                        || $handleNode->getAttribute('owner')
                        || $handleNode->getAttribute('parent')
                    ) {
                        $handles[] = $handleNode;
                    }
                }

                /** @var \Magento\View\Layout\Element $node */
                $errors = array();
                foreach ($handles as $node) {
                    $this->_collectHierarchyErrors($node, $xml, $errors);
                }

                if ($errors) {
                    $this->fail("There are errors while checking the page type and fragment types hierarchy at:\n"
                        . var_export($errors, 1)
                    );
                }
            },
            $this->areasAndThemesDataProvider()
        );
    }

    /**
     * Composes full layout xml for designated parameters
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return \Magento\View\Layout\Element
     */
    protected function _composeXml(\Magento\View\Design\ThemeInterface $theme)
    {
        /** @var \Magento\View\Layout\ProcessorInterface $layoutUpdate */
        $layoutUpdate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Layout\ProcessorInterface', array('theme' => $theme));
        return $layoutUpdate->getFileLayoutUpdatesXml();
    }

    /**
     * Validate node's declared position in hierarchy and add errors to the specified array if found
     *
     * @param \SimpleXMLElement $node
     * @param \Magento\View\Layout\Element $xml
     * @param array &$errors
     */
    protected function _collectHierarchyErrors($node, $xml, &$errors)
    {
        $name = $node->getName();
        $refName = $node->getAttribute('type') == \Magento\Core\Model\Layout\Merge::TYPE_FRAGMENT
            ? $node->getAttribute('owner') : $node->getAttribute('parent');
        if ($refName) {
            $refNode = $xml->xpath("/layouts/{$refName}");
            if (!$refNode) {
                if ($refName == 'checkout_cart_configure' || $refName == 'checkout_cart_configurefailed') {
                    $this->markTestIncomplete('MAGETWO-9182');
                }
                $errors[$name][] = "Node '{$refName}', referenced in hierarchy, does not exist";
            } elseif ($refNode[0]->getAttribute('type') == \Magento\Core\Model\Layout\Merge::TYPE_FRAGMENT) {
                $errors[$name][] = "Page fragment type '{$refName}', cannot be an ancestor in a hierarchy";
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
        $result = array();
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Design\ThemeInterface')->getCollection();
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            $result[] = array($theme);
        }
        return $result;
    }

    public function testHandleLabels()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param \Magento\View\Design\ThemeInterface $theme
             */
            function (\Magento\View\Design\ThemeInterface $theme) {
                $xml = $this->_composeXml($theme);

                $xpath = '/layouts/*['
                    . '@type="' . \Magento\Core\Model\Layout\Merge::TYPE_PAGE . '"'
                    . ' or @type="' . \Magento\Core\Model\Layout\Merge::TYPE_FRAGMENT . '"]';
                $handles = $xml->xpath($xpath) ?: array();

                /** @var \Magento\View\Layout\Element $node */
                $errors = array();
                foreach ($handles as $node) {
                    if (!$node->xpath('@label')) {
                        $nodeId = $node->getAttribute('id') ? ' id=' . $node->getAttribute('id') : '';
                        $errors[] = $node->getName() . $nodeId;
                    }
                }
                if ($errors) {
                    $this->fail(
                        'The following handles must have label, but they don\'t have it:' . PHP_EOL
                            . var_export($errors, true)
                    );
                }
            },
            $this->areasAndThemesDataProvider()
        );
    }

    public function testPageTypesDeclaration()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check whether page types are declared only in layout update files allowed for it - base ones
             */
            function (\Magento\Core\Model\Layout\File $layout) {
                $content = simplexml_load_file($layout->getFilename());
                $this->assertEmpty(
                    $content->xpath(\Magento\Core\Model\Layout\Merge::XPATH_HANDLE_DECLARATION),
                    "Theme layout update '" . $layout->getFilename() . "' contains page type declaration(s)"
                );
            },
            $this->pageTypesDeclarationDataProvider()
        );
    }

    /**
     * Get theme layout updates
     *
     * @return \Magento\Core\Model\Layout\File[]
     */
    public function pageTypesDeclarationDataProvider()
    {
        /** @var $themeUpdates \Magento\Core\Model\Layout\File\Source\Theme */
        $themeUpdates = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Layout\File\Source\Theme');
        /** @var $themeUpdatesOverride \Magento\Core\Model\Layout\File\Source\Override\Theme */
        $themeUpdatesOverride = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Layout\File\Source\Override\Theme');
        /** @var $themeCollection \Magento\Core\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Theme\Collection');
        $themeCollection->addDefaultPattern('*');
        /** @var $themeLayouts \Magento\Core\Model\Layout\File[] */
        $themeLayouts = array();
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            $themeLayouts = array_merge($themeLayouts, $themeUpdates->getFiles($theme));
            $themeLayouts = array_merge($themeLayouts, $themeUpdatesOverride->getFiles($theme));
        }
        $result = array();
        foreach ($themeLayouts as $layout) {
            $result[] = array($layout);
        }
        return $result;
    }

    public function testOverrideBaseFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check, that for an overriding file ($themeFile) in a theme ($theme), there is a corresponding base file
             *
             * @param \Magento\Core\Model\Layout\File $themeFile
             * @param \Magento\View\Design\ThemeInterface $theme
             */
            function ($themeFile, $theme) {
                $baseFiles = self::_getCachedFiles(
                    $theme->getArea(),
                    'Magento\Core\Model\Layout\File\Source\Base',
                    $theme
                );
                $fileKey = $themeFile->getModule() . '/' . $themeFile->getName();
                $this->assertArrayHasKey($fileKey, $baseFiles,
                    sprintf("Could not find base file, overridden by theme file '%s'.", $themeFile->getFilename())
                );
            },
            $this->overrideBaseFilesDataProvider()
        );
    }

    public function testOverrideThemeFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check, that for an ancestor-overriding file ($themeFile) in a theme ($theme),
             * there is a corresponding file in that ancestor theme
             *
             * @param \Magento\Core\Model\Layout\File $themeFile
             * @param \Magento\View\Design\ThemeInterface $theme
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
                        'Could not find ancestor theme "%s", '
                            . 'its layout file is supposed to be overridden by file "%s".',
                        $themeFile->getTheme()->getCode(),
                        $themeFile->getFilename()
                    )
                );

                // Search for the overridden file in the ancestor theme
                $ancestorFiles = self::_getCachedFiles($ancestorTheme->getFullPath(),
                    'Magento\Core\Model\Layout\File\Source\Theme', $ancestorTheme);
                $fileKey = $themeFile->getModule() . '/' . $themeFile->getName();
                $this->assertArrayHasKey($fileKey, $ancestorFiles,
                    sprintf("Could not find original file in '%s' theme, overridden by file '%s'.",
                        $themeFile->getTheme()->getCode(), $themeFile->getFilename())
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
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return \Magento\Core\Model\Layout\File[]
     */
    protected static function _getCachedFiles($cacheKey, $sourceClass, \Magento\View\Design\ThemeInterface $theme)
    {
        if (!isset(self::$_cachedFiles[$cacheKey])) {
            /* @var $fileList \Magento\Core\Model\Layout\File[] */
            $fileList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create($sourceClass)->getFiles($theme);
            $files = array();
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
                ->create('Magento\Core\Model\Layout\File\Source\Override\Base')
        );
    }

    /**
     * @return array
     */
    public function overrideThemeFilesDataProvider()
    {
        return $this->_retrieveFilesForEveryTheme(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create('Magento\Core\Model\Layout\File\Source\Override\Theme')
        );
    }

    /**
     * Scan all the themes in the system, for each theme retrieve list of files via $filesRetriever,
     * and return them as array of pairs [file, theme].
     *
     * @param \Magento\Core\Model\Layout\File\SourceInterface $filesRetriever
     * @return array
     */
    protected function _retrieveFilesForEveryTheme(\Magento\Core\Model\Layout\File\SourceInterface $filesRetriever)
    {
        $result = array();
        /** @var $themeCollection \Magento\Core\Model\Theme\Collection */
        $themeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Theme\Collection');
        $themeCollection->addDefaultPattern('*');
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            foreach ($filesRetriever->getFiles($theme) as $file) {
                $result[] = array($file, $theme);
            }
        }
        return $result;
    }
}
