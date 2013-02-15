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
 * Theme js file model class
 *
 * @method array getJsOrderData()
 * @method Mage_Core_Model_Theme_Customization_Files_Js setJsOrderData(array)
 * @method bool hasJsOrderData()
 */
class Mage_Core_Model_Theme_Customization_Files_Js extends Mage_Core_Model_Theme_Customization_Files_FilesAbstract
{
    /**
     * Css file type customization
     */
    const TYPE = 'js_file';

    /**
     * @var array
     */
    protected $_dataForDelete;

    /**
     * Return js file customization type
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Return file type
     *
     * @return string
     */
    protected function _getFileType()
    {
        return Mage_Core_Model_Theme_Files::TYPE_JS;
    }

    /**
     * Sets data for files deletion
     *
     * @param array $data
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    public function setDataForDelete(array $data)
    {
        $this->_dataForDelete = $data;
        return $this;
    }

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     * @return Mage_Core_Model_Theme_Customization_Files_FilesAbstract
     */
    public function saveData(Mage_Core_Model_Theme_Customization_CustomizedInterface $theme)
    {
        if (null !== $this->_dataForDelete) {
            $this->_delete($theme);
        }
        parent::saveData($theme);
        if ($this->hasJsOrderData()) {
            $this->_reorder($theme, $this->getJsOrderData());
        }

        return $this;
    }

    /**
     * Delete js files from theme
     *
     * @param $theme Mage_Core_Model_Theme
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    protected function _delete(Mage_Core_Model_Theme $theme)
    {
        /** @var $jsCollection Mage_Core_Model_Resource_Theme_Files_Collection */
        $jsCollection = $this->getCollectionByTheme($theme);
        /** @var $jsFile Mage_Core_Model_Theme_Files */
        foreach ($jsCollection as $jsFile) {
            if (in_array($jsFile->getId(), $this->_dataForDelete)) {
                $jsFile->delete();
            }
        }

        return $this;
    }

    /**
     * Remove temporary files
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    public function removeTemporaryFiles($theme)
    {
        /** @var $jsFiles Mage_Core_Model_Resource_Theme_Files_Collection */
        $jsFiles = $this->_themeFiles->getCollection()
            ->addFilter('is_temporary', true)
            ->addFilter('theme_id', $theme->getId())
            ->addFilter('file_type', Mage_Core_Model_Theme_Files::TYPE_JS);

        /** @var $file Mage_Core_Model_Theme_Files */
        foreach ($jsFiles as $file) {
            $file->delete();
        }

        return $this;
    }

    /**
     * Save form data
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    protected function _save($theme)
    {
        $themeFile = $this->_themeFiles;
        $themeJsFiles = (array)$this->_dataForSave;
        foreach ($themeJsFiles as $fileId) {
            $themeFile->load($fileId);
            if ($themeFile->getId() && ($themeFile->getThemeId() == $theme->getId())) {
                $themeFile->setIsTemporary(false)->save();
            }
        }
        return $this;
    }

    /**
     * Save js file
     *
     * @param Mage_Core_Model_Theme $theme
     * @param array $file
     * @param bool $temporary
     * @return Mage_Core_Model_Theme_Files
     */
    public function saveJsFile($theme, $file, $temporary = true)
    {
        $newFileModel = $this->_themeFiles->unsetData();
        return $newFileModel->addData(array(
            'theme_id'  => $theme->getId(),
            'file_path' => 'js/' . $this->_prepareFileName($theme, $file['name']),
            'file_type' => Mage_Core_Model_Theme_Files::TYPE_JS,
            'content'   => $file['content'],
            'is_temporary' => $temporary
        ))->save();
    }

    /**
     * Prepare file name
     *
     * @param Mage_Core_Model_Theme $theme
     * @param string $newFileName
     * @return string
     */
    protected function _prepareFileName($theme, $newFileName)
    {
        $fileInfo = pathinfo($newFileName);
        $index = 1;
        while ($this->_getThemeFileByName($theme, $newFileName)->getId()) {
            $newFileName = $fileInfo['filename'] . '_' . $index . '.' . $fileInfo['extension'];
            $index++;
        }

        return $newFileName;
    }

    /**
     * Get theme js files by name
     *
     * @param Mage_Core_Model_Theme $theme
     * @param string $fileName
     * @return Mage_Core_Model_Resource_Theme_Files_Collection
     */
    protected function _getThemeFileByName($theme, $fileName)
    {
        /** @var $jsFile Mage_Core_Model_Resource_Theme_Files_Collection */
        $jsFile = parent::getCollectionByTheme($theme)
            ->addFieldToFilter('file_path', array('like' => "%{$fileName}"))
            ->getFirstItem();

        return $jsFile;
    }

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     * @param string $order
     * @return Mage_Core_Model_Resource_Theme_Files_Collection|Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getCollectionByTheme(
        Mage_Core_Model_Theme_Customization_CustomizedInterface $theme,
        $order = Varien_Data_Collection::SORT_ORDER_ASC
    ) {
        return parent::getCollectionByTheme($theme)->setDefaultOrder($order);
    }

    /**
     * Reorder theme JS files
     *
     * @param Mage_Core_Model_Theme $theme
     * @param array $orderData
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    public function _reorder(Mage_Core_Model_Theme $theme, $orderData)
    {
        /** @var $collection Mage_Core_Model_Resource_Theme_Files_Collection */
        $collection = $this->getCollectionByTheme($theme);
        /** @var $file Mage_Core_Model_Theme_Files */
        foreach ($collection as $file) {
            $position = array_search($file->getId(), $orderData);
            if ($position === false) {
                //uploaded files will be on top
                $file->setSortOrder(0);
            }
            $file->setSortOrder($position + 1);
        }
        $collection->save();

        return $this;
    }
}
