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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity\Theme;

class ViewFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager();
        $this->viewFileSystem = $this->objectManager->get('Magento\View\FileSystem');
        $this->filesystem = $this->objectManager->get('Magento\App\Filesystem');
        $this->objectManager->configure(array(
            'preferences' => array('Magento\Core\Model\Theme' => 'Magento\Core\Model\Theme\Data')
        ));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testViewLessFilesPreProcessing()
    {
        $errorHandlerMock = $this->getMock('Magento\Less\PreProcessor\ErrorHandlerInterface',
            array('processException'));
        $this->objectManager->addSharedInstance($errorHandlerMock, 'Magento\Less\PreProcessor\ErrorHandler');
        $errorHandlerMock->expects($this->any())->method('processException')->will($this->returnCallback(
            function ($exception) {
                /** @var $exception \Exception */
                $this->fail($exception->getMessage());
            }
        ));
        /** @var $lessPreProcessor \Magento\Less\PreProcessor */
        $lessPreProcessor = $this->objectManager->create('Magento\Less\PreProcessor');
        $directoryRead = $this->filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
        /**
         * Solution for \Magento\View\Layout\File\Source\Base aggregator, it depends on theme and area
         */
        $theme = $this->objectManager->create('Magento\View\Design\ThemeInterface');
        $theme->setArea('frontend');
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             * @param string $area
             */
            function ($file, $area) use ($lessPreProcessor, $directoryRead, $theme) {
                $fileInfo = pathinfo($file);
                if ($fileInfo['extension'] == 'css') {
                    $lessFile = "{$fileInfo['dirname']}/{$fileInfo['filename']}.less";
                    $params = array('area' => $area, 'themeModel' => $theme);
                    $cssSourceFile = $this->viewFileSystem->getViewFile($file, $params);
                    $lessSourceFile = $this->viewFileSystem->getViewFile($lessFile, $params);
                    if ($directoryRead->isExist($directoryRead->getRelativePath($cssSourceFile))
                        && $directoryRead->isExist($directoryRead->getRelativePath($lessSourceFile))
                    ) {
                        $this->fail("Duplicate files: '{$lessSourceFile}', '{$cssSourceFile}'");
                    } elseif ($directoryRead->isExist($directoryRead->getRelativePath($lessSourceFile))) {
                        $this->assertFileExists($lessPreProcessor->processLessInstructions($lessFile, $params));
                    }
                }
            },
            $this->viewFilesFromThemesDataProvider([$theme])
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testViewFilesFromThemes()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             * @param string $area
             * @param string $themeId
             */
            function ($file, $area, $themeId) {
                $params = array('area' => $area, 'themeId' => $themeId);
                $viewFile = $this->viewFileSystem->getViewFile($file, $params);
                $this->assertFileExists($viewFile);

                $fileParts = explode(\Magento\View\Service::SCOPE_SEPARATOR, $file);
                if (count($fileParts) > 1) {
                    $params['module'] = $fileParts[0];
                }
                if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                    $files = array();
                    $content = file_get_contents($viewFile);
                    preg_match_all(\Magento\View\Url\CssResolver::REGEX_CSS_RELATIVE_URLS, $content, $matches);
                    foreach ($matches[1] as $relativePath) {
                        $path = $this->_addCssDirectory($relativePath, $file);
                        $pathFile = $this->viewFileSystem->getViewFile($path, $params);
                        if (!is_file($pathFile)) {
                            $files[] = $relativePath;
                        }
                    }
                    if (!empty($files)) {
                        $this->fail('Cannot find file(s): ' . implode(', ', $files));
                    }
                }
            },
            $this->viewFilesFromThemesDataProvider($this->_getDesignThemes())
        );
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
     * @param \Magento\Core\Model\Theme[] $themes
     * @return array
     */
    public function viewFilesFromThemesDataProvider($themes)
    {
        // Find files, declared in views
        $files = array();
        foreach ($themes as $theme) {
            $this->_collectViewUrlInvokes($theme, $files);
            $this->_collectViewLayoutDeclarations($theme, $files);
        }

        // Populate data provider in correspondence of themes to view files
        $result = array();
        foreach ($themes as $theme) {
            if (!isset($files[$theme->getId()])) {
                continue;
            }
            foreach (array_unique($files[$theme->getId()]) as $file) {
                $result["{$theme->getId()}/{$file}"] = array(
                    'file'  => $file,
                    'area'  => $theme->getArea(),
                    'theme' => $theme->getId()
                );
            }
        }
        return array_values($result);
    }

    /**
     * Collect getViewFileUrl() from theme templates
     *
     * @param \Magento\Core\Model\Theme $theme
     * @param array &$files
     */
    protected function _collectViewUrlInvokes($theme, &$files)
    {
        $searchDir = $theme->getCustomization()->getThemeFilesPath();
        if (empty($searchDir)) {
            return;
        }
        $dirLength = strlen($searchDir);
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($searchDir)) as $fileInfo) {
            // Check that file path is valid
            $relativePath = substr($fileInfo->getPath(), $dirLength);
            if ($this->_validateTemplatePath($relativePath)) {
                // Scan file for references to other files
                foreach ($this->_findReferencesToViewFile($fileInfo) as $file) {
                    $files[$theme->getId()][] = $file;
                }
            }
        }
    }

    /**
     * Collect view files declarations into layout
     *
     * @param \Magento\Core\Model\Theme $theme
     * @param array &$files
     */
    protected function _collectViewLayoutDeclarations($theme, &$files)
    {
        // Collect "addCss" and "addJs" from theme layout
        /** @var \Magento\View\Layout\ProcessorInterface $layoutUpdate */
        $layoutUpdate = $this->objectManager->create('Magento\View\Layout\ProcessorInterface',
            array('theme' => $theme));
        $fileLayoutUpdates = $layoutUpdate->getFileLayoutUpdatesXml();
        $elements = $fileLayoutUpdates->xpath(
            '//block[@class="Magento\Theme\Block\Html\Head\Css" or @class="Magento\Theme\Block\Html\Head\Script"]'
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
        $parts = explode('/', $relativePath);
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
     * @param \SplFileInfo $fileInfo
     * @return array
     */
    protected function _findReferencesToViewFile(\SplFileInfo $fileInfo)
    {
        $result = array();
        if (preg_match_all(
            '/\$this->getViewFileUrl\(\'([^\']+?)\'\)/', file_get_contents($fileInfo->getRealPath()), $matches)
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
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Filesystem')->getPath('jslib')
                . '/' . $file
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
