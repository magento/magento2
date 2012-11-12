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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme model class
 *
 * @method Mage_Core_Model_Theme save()
 * @method string getThemeCode()
 * @method string getParentTheme()
 * @method string getThemePath()
 * @method Mage_Core_Model_Theme setParentTheme(string $parentTheme)
 * @method setPreviewImage(string $previewImage)
 * @method string getPreviewImage()
 */
class Mage_Core_Model_Theme extends Mage_Core_Model_Abstract
{
    /**
     * Theme directory
     */
    const THEME_DIR = 'theme';

    /**
     * Preview image directory
     */
    const IMAGE_DIR_PREVIEW = 'preview';

    /**
     * Origin image directory
     */
    const IMAGE_DIR_ORIGIN = 'origin';

    /**
     * Preview image width
     */
    const PREVIEW_IMAGE_WIDTH = 200;

    /**
     * Preview image height
     */
    const PREVIEW_IMAGE_HEIGHT = 200;

    /**
     * @var Varien_Io_File
     */
    protected $_ioFile;

    /**
     * Theme model initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Theme');
    }

    /**
     * Filesystem client
     *
     * @return Varien_Io_File
     */
    protected function _getIoFile()
    {
        if (!$this->_ioFile) {
            $this->_ioFile = new Varien_Io_File();
        }
        return $this->_ioFile;
    }

    /**
     * Themes collection loaded from file system configurations
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    public function getCollectionFromFilesystem()
    {
        return Mage::getModel('Mage_Core_Model_Theme_Collection');
    }

    /**
     * Loads data that contains in configuration file (theme.xml)
     *
     * @param string $configPath
     * @return Mage_Core_Model_Theme
     */
    public function loadFromConfiguration($configPath)
    {
        $themeConfig = $this->_getConfigModel(array($configPath));

        $packageCodes = $themeConfig->getPackageCodes();
        $packageCode = reset($packageCodes);
        $themeCodes = $themeConfig->getPackageThemeCodes($packageCode);
        $themeCode = reset($themeCodes);

        $themeVersions = $themeConfig->getCompatibleVersions($packageCode, $themeCode);
        $media = $themeConfig->getMedia($packageCode, $themeCode);
        $this->setData(array(
            'theme_code'           => $themeCode,
            'theme_title'          => $themeConfig->getThemeTitle($packageCode, $themeCode),
            'theme_version'        => $themeConfig->getThemeVersion($packageCode, $themeCode),
            'parent_theme'         => $themeConfig->getParentTheme($packageCode, $themeCode),
            'is_featured'          => $themeConfig->getFeatured($packageCode, $themeCode),
            'magento_version_from' => $themeVersions['from'],
            'magento_version_to'   => $themeVersions['to'],
            'theme_path'           => $packageCode . '/' . $themeCode,
            'preview_image'        => $media['preview_image'] ? $media['preview_image'] : null,
            'theme_directory'      => dirname($configPath),
        ));
        return $this;
    }

    /**
     * Return configuration model for themes
     *
     * @param array $configPaths
     * @return Magento_Config_Theme
     */
    protected function _getConfigModel(array $configPaths)
    {
        return new Magento_Config_Theme($configPaths);
    }

    /**
     * Validate theme data
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Theme
     */
    protected function _validate()
    {
        /** @var $validator Mage_Core_Model_Theme_Validator */
        $validator = Mage::getModel('Mage_Core_Model_Theme_Validator');
        if (!$validator->validate($this)) {
            $messages = $validator->getErrorMessages();
            Mage::throwException(implode(PHP_EOL, reset($messages)));
        }
        return $this;
    }

    /**
     * Check theme is existing in filesystem
     *
     * @return bool
     */
    public function isDeletable()
    {
        $collection = $this->getCollectionFromFilesystem()->addDefaultPattern()->getItems();
        return !($this->getThemePath() && isset($collection[$this->getThemePath()]));
    }

    /**
     * Update all child themes relations
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _updateChildRelations()
    {
        $parentThemeId = $this->getParentId();
        /** @var $childThemes Mage_Core_Model_Resource_Theme_Collection */
        $childThemes = $this->getCollection();
        $childThemes->addFieldToFilter('parent_id', array('eq' => $this->getId()))->load();

        /** @var $theme Mage_Core_Model_Theme */
        foreach ($childThemes->getItems() as $theme) {
            $theme->setParentId($parentThemeId)->save();
        }

        return $this;
    }

    /**
     * Before theme save
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _beforeSave()
    {
        $this->_validate();
        return parent::_beforeSave();
    }

    /**
     * Processing theme before deleting data
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Theme
     */
    protected function _beforeDelete()
    {
        if (!$this->isDeletable()) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Current theme isn\'t deletable.'));
        }
        $this->removePreviewImage();
        return parent::_beforeDelete();
    }

    /**
     * Update all relations after deleting theme
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _afterDelete()
    {
        $this->_updateChildRelations();
        return parent::_afterDelete();
    }

    /**
     * Save preview image
     *
     * @return Mage_Core_Model_Theme
     */
    public function savePreviewImage()
    {
        if (!$this->getPreviewImage() || !$this->getThemeDirectory()) {
            return $this;
        }
        $currentWorkingDir = getcwd();

        chdir($this->getThemeDirectory());

        $imagePath = realpath($this->getPreviewImage());

        if (0 === strpos($imagePath, $this->getThemeDirectory())) {
            $this->createPreviewImage($imagePath);
        }

        chdir($currentWorkingDir);

        return $this;
    }

    /**
     * Get themes root directory absolute path
     *
     * @return string
     */
    protected static function _getPreviewImagePublishedRootDir()
    {
        $fileSystemHelper = new Varien_Io_File();
        $dirPath = Mage::getBaseDir('media') . DS . self::THEME_DIR;
        $fileSystemHelper->checkAndCreateFolder($dirPath);
        return $dirPath;
    }

    /**
     * Get directory path for origin image
     *
     * @return string
     */
    public static function getImagePathOrigin()
    {
        return self::_getPreviewImagePublishedRootDir() . DS . self::IMAGE_DIR_ORIGIN;
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    protected static function _getImagePathPreview()
    {
        return self::_getPreviewImagePublishedRootDir() . DS . self::IMAGE_DIR_PREVIEW;
    }

    /**
     * Get preview image directory url
     *
     * @return string
     */
    public static function getPreviewImageDirectoryUrl()
    {
        return Mage::getBaseUrl('media') . self::THEME_DIR . '/' . self::IMAGE_DIR_PREVIEW . '/';
    }

    /**
     * Save data from form
     *
     * @param array $themeData
     * @return Mage_Core_Model_Theme
     */
    public function saveFormData($themeData)
    {
        if (isset($themeData['theme_id'])) {
            $this->load($themeData['theme_id']);
        }
        $previewImageData = array();
        if (isset($themeData['preview_image'])) {
            $previewImageData = $themeData['preview_image'];
            unset($themeData['preview_image']);
        }
        $this->addData($themeData);

        if (isset($previewImageData['delete'])) {
            $this->removePreviewImage();
        }

        $this->uploadPreviewImage('preview_image');
        $this->save();
        return $this;
    }

    /**
     * Upload and create preview image
     *
     * @throws Mage_Core_Exception
     * @param string $scope the request key for file
     * @return bool
     */
    public function uploadPreviewImage($scope)
    {
        $adapter  = new Zend_File_Transfer_Adapter_Http();
        if (!$adapter->isUploaded($scope)) {
            return false;
        }
        if (!$adapter->isValid($scope)) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Uploaded image is not valid'));
        }
        $upload = new Varien_File_Uploader($scope);
        $upload->setAllowCreateFolders(true);
        $upload->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'));
        $upload->setAllowRenameFiles(true);
        $upload->setFilesDispersion(false);

        if (!$upload->save(self::getImagePathOrigin())) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Image can not be saved.'));
        }

        $fileName = self::getImagePathOrigin() . DS . $upload->getUploadedFileName();
        $this->removePreviewImage();
        $this->createPreviewImage($fileName);

        $this->_getIoFile()->rm($fileName);

        return true;
    }

    /**
     * Create preview image
     *
     * @param string $imagePath
     * @return string
     */
    public function createPreviewImage($imagePath)
    {
        $adapter = Mage::helper('Mage_Core_Helper_Data')->getImageAdapterType();
        $image = new Varien_Image($imagePath, $adapter);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->resize(self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getMimeType());
        $image->save(self::_getImagePathPreview(), $imageName);

        $this->setPreviewImage($imageName);

        return $imageName;
    }

    /**
     * Delete preview image
     *
     * @return Mage_Core_Model_Theme
     */
    public function removePreviewImage()
    {
        $previewImage = $this->getPreviewImage();
        $this->setPreviewImage('');
        if ($previewImage) {
            $this->_getIoFile()->rm(self::_getImagePathPreview() . DS . $previewImage);
        }
        return $this;
    }

    /**
     * Get url for themes preview image
     *
     * @return string
     */
    public function getPreviewImageUrl()
    {
        if (!$this->getPreviewImage()) {
            return $this->_getPreviewImageDefaultUrl();
        }
        return self::getPreviewImageDirectoryUrl() . $this->getPreviewImage();
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    protected function _getPreviewImageDefaultUrl()
    {
        return Mage::getDesign()->getSkinUrl('Mage_Core::theme/default_preview.jpg');
    }

    /**
     * Get skin list
     *
     * @return array
     */
    public function getSkinList()
    {
        $result = array();
        $skinPaths = glob($this->_getSkinFolderPattern(), GLOB_ONLYDIR);

        foreach ($skinPaths as $skinPath) {
            $skinPath = str_replace(DS, '/', $skinPath);
            if (preg_match('/\/(?P<skin>[^\/.]+)$/i', $skinPath, $skinMatches)) {
                $result[$skinMatches['skin']] = implode('/', array(
                    $this->getThemePath(),
                    $skinMatches['skin']
                ));
            }
        }
        return $result;
    }

    /**
     * Get skin folder pattern
     *
     * @return string
     */
    protected function _getSkinFolderPattern()
    {
        return implode(DS, array(
            Mage::getBaseDir('design'),
            Mage_Core_Model_Design_Package::DEFAULT_AREA,
            $this->getThemePath(),
            'skin',
            '*'
        ));
    }
}
