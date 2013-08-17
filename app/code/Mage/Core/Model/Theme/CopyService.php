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
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Theme_FileFactory
     */
    protected $_fileFactory;

    /**
     * @var Mage_Core_Model_Layout_Link
     */
    protected $_link;

    /**
     * @var Mage_Core_Model_Layout_UpdateFactory
     */
    protected $_updateFactory;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @var Mage_Core_Model_Theme_Customization_Path
     */
    protected $_customizationPath;

    /**
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Theme_FileFactory $fileFactory
     * @param Mage_Core_Model_Layout_Link $link
     * @param Mage_Core_Model_Layout_UpdateFactory $updateFactory
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Theme_Customization_Path $customization
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Theme_FileFactory $fileFactory,
        Mage_Core_Model_Layout_Link $link,
        Mage_Core_Model_Layout_UpdateFactory $updateFactory,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Theme_Customization_Path $customization
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileFactory = $fileFactory;
        $this->_link = $link;
        $this->_updateFactory = $updateFactory;
        $this->_eventManager = $eventManager;
        $this->_customizationPath = $customization;
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
        $this->_copyLayoutCustomization($source, $target);
        $this->_copyFilesystemCustomization($source, $target);
        $this->_eventManager->dispatch('theme_copy_after', array('sourceTheme' => $source, 'targetTheme' => $target));
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
        foreach ($target->getCustomization()->getFiles() as $themeFile) {
            $themeFile->delete();
        }
        /** @var $newFile Mage_Core_Model_Theme_File */
        foreach ($source->getCustomization()->getFiles() as $themeFile) {
            /** @var $newThemeFile Mage_Core_Model_Theme_File */
            $newThemeFile = $this->_fileFactory->create();
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
     * Add layout links to general layout updates for themes
     *
     * @param Mage_Core_Model_Theme $source
     * @param Mage_Core_Model_Theme $target
     */
    protected function _copyLayoutCustomization(Mage_Core_Model_Theme $source, Mage_Core_Model_Theme $target)
    {
        $update = $this->_updateFactory->create();
        /** @var $targetUpdates Mage_Core_Model_Resource_Layout_Update_Collection */
        $targetUpdates = $update->getCollection();
        $targetUpdates->addThemeFilter($target->getId());
        $targetUpdates->delete();

        /** @var $sourceCollection Mage_Core_Model_Resource_Layout_Link_Collection */
        $sourceCollection = $this->_link->getCollection();
        $sourceCollection->addThemeFilter($source->getId());
        /** @var $layoutLink Mage_Core_Model_Layout_Link */
        foreach ($sourceCollection as $layoutLink) {
            /** @var $update Mage_Core_Model_Layout_Update */
            $update = $this->_updateFactory->create();
            $update->load($layoutLink->getLayoutUpdateId());
            if ($update->getId()) {
                $update->setId(null);
                $update->save();
                $layoutLink->setThemeId($target->getId());
                $layoutLink->setLayoutUpdateId($update->getId());
                $layoutLink->setId(null);
                $layoutLink->save();
            }
        }
    }

    /**
     * Copy customizations stored in a file system from one theme to another, overriding existing data
     *
     * @param Mage_Core_Model_Theme $source
     * @param Mage_Core_Model_Theme $target
     */
    protected function _copyFilesystemCustomization(Mage_Core_Model_Theme $source, Mage_Core_Model_Theme $target)
    {
        $sourcePath = $this->_customizationPath->getCustomizationPath($source);
        $targetPath = $this->_customizationPath->getCustomizationPath($target);

        if (!$sourcePath || !$targetPath) {
            return;
        }

        $this->_deleteFilesRecursively($targetPath);

        if ($this->_filesystem->isDirectory($sourcePath)) {
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

    /**
     * Delete all files in a directory recursively
     *
     * @param string $targetDir
     */
    protected function _deleteFilesRecursively($targetDir)
    {
        foreach ($this->_filesystem->searchKeys($targetDir, '*') as $path) {
            $this->_filesystem->delete($path);
        }
    }
}
