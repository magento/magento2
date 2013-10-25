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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity\Theme;

class ViewFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testViewFilesFromThemes()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $area
             * @param string $themeId
             * @param string $file
             * @throws \PHPUnit_Framework_AssertionFailedError|Exception
             */
            function ($area, $themeId, $file) {
                $this->_markTestIncompleteDueToBug($area, $file);
                try {
                    $params = array('area' => $area, 'themeId' => $themeId);
                    $viewFile = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()
                        ->get('Magento\Core\Model\View\FileSystem')
                        ->getViewFile($file, $params);
                    $this->assertFileExists($viewFile);

                    $fileParts = explode(\Magento\Core\Model\View\Service::SCOPE_SEPARATOR, $file);
                    if (count($fileParts) > 1) {
                        $params['module'] = $fileParts[0];
                    }
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                        $errors = array();
                        $content = file_get_contents($viewFile);
                        preg_match_all(\Magento\Core\Helper\Css::REGEX_CSS_RELATIVE_URLS, $content, $matches);
                        foreach ($matches[1] as $relativePath) {
                            $path = $this->_addCssDirectory($relativePath, $file);
                            $pathFile = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()
                                ->get('Magento\Core\Model\View\FileSystem')
                                ->getViewFile($path, $params);
                            if (!is_file($pathFile)) {
                                $errors[] = $relativePath;
                            }
                        }
                        if (!empty($errors)) {
                            $this->fail('Cannot find file(s): ' . implode(', ', $errors));
                        }
                    }
                } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                    throw $e;
                } catch (\Exception $e) {
                    $this->fail($e->getMessage());
                }
            },
            $this->viewFilesFromThemesDataProvider()
        );
    }

    /**
     * This dummy method was introduced to circumvent cyclomatic complexity check
     *
     * @param string $area
     * @param string $file
     */
    protected function _markTestIncompleteDueToBug($area, $file)
    {
        if ($area === 'frontend' && in_array($file, array('js/selectivizr-min.js', 'css/styles.css'))
            || $area === 'adminhtml' && in_array($file, array('mui/reset.css'))
        ) {
            $this->markTestIncomplete('MAGETWO-9806, MAGETWO-12325');
        }
    }

    /**
     * Analyze path to a file in CSS url() directive and add the original CSS-file relative path to it
     *
     * @param string $relativePath
     * @param string $sourceFile
     * @return string
     * @throws \Exception if the specified relative path cannot be apparently resolved
     */
    protected function _addCssDirectory($relativePath, $sourceFile)
    {
        if (strpos($relativePath, '::') > 0) {
            return $relativePath;
        }
        $file = dirname($sourceFile) . '/' . $relativePath;
        $parts = explode('/', $file);
        $result = array();
        foreach ($parts as $part) {
            if ('..' === $part) {
                if (null === array_pop($result)) {
                    throw new \Exception('Invalid file: ' . $file);
                }
            } elseif ('.' !== $part) {
                $result[] = $part;
            }

        }
        return implode('/', $result);
    }

    /**
     * Collect getViewUrl() and similar calls from themes
     *
     * @return array
     */
    public function viewFilesFromThemesDataProvider()
    {
        $themes = $this->_getDesignThemes();

        // Find files, declared in views
        $files = array();
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themes as $theme) {
            $this->_collectGetViewUrlInvokes($theme, $files);
        }

        // Populate data provider in correspondence of themes to view files
        $result = array();
        /** @var $theme \Magento\View\Design\ThemeInterface */
        foreach ($themes as $theme) {
            if (!isset($files[$theme->getId()])) {
                continue;
            }
            foreach (array_unique($files[$theme->getId()]) as $file) {
                $result["{$theme->getId()}/{$file}"] = array($theme->getArea(), $theme->getId(), $file);
            }
        }
        return array_values($result);
    }

    /**
     * Collect getViewUrl() from theme templates
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param array &$files
     */
    protected function _collectGetViewUrlInvokes($theme, &$files)
    {
        $searchDir = $theme->getCustomization()->getThemeFilesPath();
        $dirLength = strlen($searchDir);
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($searchDir)) as $fileInfo) {
            // Check that file path is valid
            $relativePath = substr($fileInfo->getPath(), $dirLength);
            if (!$this->_validateTemplatePath($relativePath)) {
                continue;
            }

            // Scan file for references to other files
            foreach ($this->_findReferencesToViewFile($fileInfo) as $file) {
                $files[$theme->getId()][] = $file;
            }
        }

        // Collect "addCss" and "addJs" from theme layout
        /** @var \Magento\View\Layout\ProcessorInterface $layoutUpdate */
        $layoutUpdate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Layout\ProcessorInterface', array('theme' => $theme));
        $fileLayoutUpdates = $layoutUpdate->getFileLayoutUpdatesXml();
        $elements = $fileLayoutUpdates->xpath(
            '//block[@class="Magento\Page\Block\Html\Head\Css" or @class="Magento\Page\Block\Html\Head\Script"]'
                . '/arguments/argument[@name="file"]'
        );
        if ($elements) {
            foreach ($elements as $filenameNode) {
                $viewFile = (string)$filenameNode;
                if ($this->_isFileForDisabledModule($viewFile)) {
                    continue;
                }
                $files[$theme->getId()][] = $viewFile;
            }
        }
    }

    /**
     * Checks file path - whether there are mentions of disabled modules
     *
     * @param string $relativePath
     * @return bool
     */
    protected function _validateTemplatePath($relativePath)
    {
        if (!preg_match('/\.phtml$/', $relativePath)) {
            return false;
        }
        $relativePath = trim($relativePath, '/\\');
        $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
        $enabledModules = $this->_getEnabledModules();
        foreach ($parts as $part) {
            if (!preg_match('/^[A-Z][[:alnum:]]*_[A-Z][[:alnum:]]*$/', $part)) {
                continue;
            }
            if (!isset($enabledModules[$part])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Scan specified file for getViewUrl() pattern
     *
     * @param SplFileInfo $fileInfo
     * @return array
     */
    protected function _findReferencesToViewFile(SplFileInfo $fileInfo)
    {
        $result = array();
        if (preg_match_all(
            '/\$this->getViewUrl\(\'([^\']+?)\'\)/', file_get_contents($fileInfo->getRealPath()), $matches)
        ) {
            foreach ($matches[1] as $viewFile) {
                if ($this->_isFileForDisabledModule($viewFile)) {
                    continue;
                }
                $result[] = $viewFile;
            }
        }
        return $result;
    }

    /**
     * @param string $file
     * @dataProvider staticLibsDataProvider
     */
    public function testStaticLibs($file)
    {
        $this->markTestIncomplete('Should be fixed when static when we have static folder jslib implemented');
        $this->assertFileExists(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir')->getDir('jslib')
                . DIRECTORY_SEPARATOR . $file
        );
    }

    /**
     * @return array
     */
    public function staticLibsDataProvider()
    {
        return array(
            array('media/editor.swf'),
            array('media/flex.swf'), // looks like this one is not used anywhere
            array('media/uploader.swf'),
            array('media/uploaderSingle.swf'),
        );
    }
}
