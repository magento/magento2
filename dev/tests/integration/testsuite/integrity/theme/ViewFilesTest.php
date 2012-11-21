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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Theme_ViewFilesTest extends Magento_Test_TestCase_IntegrityAbstract
{
    /**
     * @param string $application
     * @param string $package
     * @param string $theme
     * @param string $file
     * @dataProvider viewFilesFromThemesDataProvider
     */
    public function testViewFilesFromThemes($application, $package, $theme, $file)
    {
        $params = array(
            'area'    => $application,
            'package' => $package,
            'theme'   => $theme
        );
        $viewFile = Mage::getDesign()->getViewFile($file, $params);
        $this->assertFileExists($viewFile);

        $fileParts = explode(Mage_Core_Model_Design_Package::SCOPE_SEPARATOR, $file);
        if (count($fileParts) > 1) {
            $params['module'] = $fileParts[0];
        }
        if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
            $errors = array();
            $content = file_get_contents($viewFile);
            preg_match_all(Mage_Core_Model_Design_Package::REGEX_CSS_RELATIVE_URLS, $content, $matches);
            foreach ($matches[1] as $relativePath) {
                $path = $this->_addCssDirectory($relativePath, $file);
                $pathFile = Mage::getDesign()->getViewFile($path, $params);
                if (!is_file($pathFile)) {
                    $errors[] = $relativePath;
                }
            }
            if (!empty($errors)) {
                $this->fail('Can not find file(s): ' . implode(', ', $errors));
            }
        }
    }

    /**
     * Analyze path to a file in CSS url() directive and add the original CSS-file relative path to it
     *
     * @param string $relativePath
     * @param string $sourceFile
     * @return string
     * @throws Exception if the specified relative path cannot be apparently resolved
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
                    throw new Exception('Invalid file: ' . $file);
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
        foreach ($themes as $themeDesign) {
            list($area, $package, $theme) = explode('/', $themeDesign);
            $this->_collectGetViewUrlInvokes($area, $package, $theme, $files);
        }

        // Populate data provider in correspondence of themes to view files
        $result = array();
        foreach ($themes as $themeDesign) {
            list($area, $package, $theme) = explode('/', $themeDesign);
            if (!isset($files[$area][$package][$theme])) {
                continue;
            }
            foreach (array_unique($files[$area][$package][$theme]) as $file) {
                $result["{$area}/{$package}/{$theme}/{$file}"] =
                    array($area, $package, $theme, $file);
            }
        }

        return array_values($result);
    }

    /**
     * Collect getViewUrl() from theme templates
     *
     * @param string    $area
     * @param string    $package
     * @param string    $theme
     * @param array     &$files
     */
    protected function _collectGetViewUrlInvokes($area, $package, $theme, &$files)
    {
        $searchDir = Mage::getBaseDir('design') . DIRECTORY_SEPARATOR . $area . DIRECTORY_SEPARATOR . $package
                . DIRECTORY_SEPARATOR . $theme;
        $dirLength = strlen($searchDir);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchDir)) as $fileInfo) {
            // Check that file path is valid
            $relativePath = substr($fileInfo->getPath(), $dirLength);
            if (!$this->_validateTemplatePath($relativePath)) {
                continue;
            }

            // Scan file for references to other files
            foreach ($this->_findReferencesToViewFile($fileInfo) as $file) {
                $files[$area][$package][$theme][] = $file;
            }
        }

        // Collect "addCss" and "addJs" from theme layout
        $layoutUpdate = Mage::getModel('Mage_Core_Model_Layout_Merge',
            array('arguments' => array('area' => $area, 'package' => $package, 'theme' => $theme))
        );
        $fileLayoutUpdates = $layoutUpdate->getFileLayoutUpdatesXml();
        $elements = $fileLayoutUpdates->xpath('//action[@method="addCss" or @method="addJs"]/*[1]');
        if ($elements) {
            foreach ($elements as $filenameNode) {
                $viewFile = (string)$filenameNode;
                if ($this->_isFileForDisabledModule($viewFile)) {
                    continue;
                }
                $files[$area][$package][$theme][] = $viewFile;
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
        $this->assertFileExists(Mage::getBaseDir('jslib') . DIRECTORY_SEPARATOR . $file);
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
