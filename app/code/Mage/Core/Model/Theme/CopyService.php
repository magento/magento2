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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Service of copying customizations from one theme to another
 */
class Mage_Core_Model_Theme_CopyService
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(Magento_ObjectManager $objectManager, Magento_Filesystem $filesystem)
    {
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
    }

    /**
     * Copy customizations from one theme to another
     *
     * @param Mage_Core_Model_Theme $source
     * @param Mage_Core_Model_Theme $target
     */
    public function copy(Mage_Core_Model_Theme $source, Mage_Core_Model_Theme $target)
    {
        $this->_copyDatabaseCustomization($source, $target);
        $this->_copyFilesystemCustomization($source, $target);
    }

    /**
     * Copy customizations stored in a database from one theme to another, overriding existing data
     *
     * @param Mage_Core_Model_Theme $source
     * @param Mage_Core_Model_Theme $target
     */
    protected function _copyDatabaseCustomization(Mage_Core_Model_Theme $source, Mage_Core_Model_Theme $target)
    {
        /** @var $themeFile Mage_Core_Model_Theme_File */
        foreach ($target->getFiles() as $themeFile) {
            $themeFile->delete();
        }
        /** @var $newFile Mage_Core_Model_Theme_File */
        foreach ($source->getFiles() as $themeFile) {
            /** @var $newThemeFile Mage_Core_Model_Theme_File */
            $newThemeFile = $this->_objectManager->create('Mage_Core_Model_Theme_File');
            $newThemeFile->setData(array(
                'theme_id'      => $target->getId(),
                'file_path'     => $themeFile->getFilePath(),
                'file_type'     => $themeFile->getFileType(),
                'content'       => $themeFile->getContent(),
                'sort_order'    => $themeFile->getData('sort_order'),
            ));
            $newThemeFile->save();
        }
    }

    /**
     * Copy customizations stored in a file system from one theme to another, overriding existing data
     *
     * @param $source
     * @param $target
     */
    protected function _copyFilesystemCustomization(Mage_Core_Model_Theme $source, Mage_Core_Model_Theme $target)
    {
        $sourcePath = $source->getCustomizationPath();
        $targetPath = $target->getCustomizationPath();
        if ($sourcePath && $targetPath && $this->_filesystem->isDirectory($sourcePath)) {
            $this->_copyFilesRecursively($sourcePath, $sourcePath, $targetPath);
        }
    }

    /**
     * Copies all files in a directory recursively
     *
     * @param string $baseDir
     * @param string $sourceDir
     * @param string $targetDir
     */
    protected function _copyFilesRecursively($baseDir, $sourceDir, $targetDir)
    {
        $this->_filesystem->setIsAllowCreateDirectories(true);
        foreach ($this->_filesystem->searchKeys($sourceDir, '*') as $path) {
            if ($this->_filesystem->isDirectory($path)) {
                $this->_copyFilesRecursively($baseDir, $path, $targetDir);
            } else {
                $filePath = substr($path, strlen($baseDir) + 1);
                $this->_filesystem->copy($path, $targetDir . '/' . $filePath, $baseDir, $targetDir);
            }
        }
    }
}
