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

namespace Magento\Test\Integrity\Modular;

class ViewFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testViewFilesFromModulesView()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $application
             * @param string $file
             */
            function ($application, $file) {
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\View\DesignInterface')
                    ->setArea($application)
                    ->setDefaultDesignTheme();
                $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Core\Model\View\FileSystem')
                    ->getViewFile($file);
                $this->assertFileExists($result);
            },
            $this->viewFilesFromModulesViewDataProvider()
        );
    }

    /**
     * Collect getViewUrl() calls from base templates
     *
     * @return array
     */
    public function viewFilesFromModulesViewDataProvider()
    {
        $files = array();
        /** @var $configModel \Magento\Core\Model\Config */
        $configModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Config');
        foreach ($this->_getEnabledModules() as $moduleName) {
            $moduleViewDir = $configModel->getModuleDir('view', $moduleName);
            if (!is_dir($moduleViewDir)) {
                continue;
            }
            $this->_findViewFilesInViewFolder($moduleViewDir, $files);
        }
        $result = array();
        foreach ($files as $area => $references) {
            foreach ($references as $file) {
                $result[] = array($area, $file);
            }
        }
        return $result;
    }

    /**
     * Find view file references per area in declared modules.
     *
     * @param string $moduleViewDir
     * @param array $files
     * @return null
     */
    protected function _findViewFilesInViewFolder($moduleViewDir, &$files)
    {
        foreach (new \DirectoryIterator($moduleViewDir) as $viewAppDir) {
            $area = $viewAppDir->getFilename();
            if (0 === strpos($area, '.') || !$viewAppDir->isDir()) {
                continue;
            }
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($viewAppDir->getRealPath())
            ) as $fileInfo) {
                $references = $this->_findReferencesToViewFile($fileInfo);
                if (!isset($files[$area])) {
                    $files[$area] = $references;
                } else {
                    $files[$area] = array_merge($files[$area], $references);
                }
                $files[$area] = array_unique($files[$area]);
            }
        }
    }

    /**
     * Scan specified file for getViewUrl() pattern
     *
     * @param \SplFileInfo $fileInfo
     * @return array
     */
    protected function _findReferencesToViewFile(\SplFileInfo $fileInfo)
    {
        if (!$fileInfo->isFile() || !preg_match('/\.phtml$/', $fileInfo->getFilename())) {
            return array();
        }

        $result = array();
        $content = file_get_contents($fileInfo->getRealPath());
        if (preg_match_all('/\$this->getViewFileUrl\(\'([^\']+?)\'\)/', $content, $matches)) {
            foreach ($matches[1] as $value) {
                if ($this->_isFileForDisabledModule($value)) {
                    continue;
                }
                $result[] = $value;
            }
        }
        return $result;
    }

    public function testViewFilesFromModulesCode()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * getViewUrl() hard-coded in the php-files
             *
             * @param string $application
             * @param string $file
             */
            function ($application, $file) {
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\View\DesignInterface')
                    ->setArea($application)
                    ->setDefaultDesignTheme();
                $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Core\Model\View\FileSystem');
                $this->assertFileExists($filesystem->getViewFile($file));
            },
            $this->viewFilesFromModulesCodeDataProvider()
        );
    }

    /**
     * @return array
     */
    public function viewFilesFromModulesCodeDataProvider()
    {
        $allFiles = array();
        foreach (glob(__DIR__ . DS . '_files' . DS . 'view_files*.php') as $file) {
            $allFiles = array_merge($allFiles, include($file));
        }
        return $this->_removeDisabledModulesFiles($allFiles);
    }

    /**
     * Scans array of file information and removes files, that belong to disabled modules.
     * Thus we won't test them.
     *
     * @param array $allFiles
     * @return array
     */
    protected function _removeDisabledModulesFiles($allFiles)
    {
        $result = array();
        foreach ($allFiles as $fileInfo) {
            $fileName = $fileInfo[1];
            if ($this->_isFileForDisabledModule($fileName)) {
                continue;
            }
            $result[] = $fileInfo;
        }
        return $result;
    }
}
