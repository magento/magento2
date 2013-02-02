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
 * Theme model class
 *
 * @method Mage_Core_Model_Theme save()
 * @method string getPackageCode()
 * @method string getThemePath()
 * @method string getThemeTitle() getThemeTitle()
 * @method string getParentThemePath()
 * @method string getPreviewImage()
 * @method string getThemeDirectory()
 * @method string getParentId()
 * @method string getArea()
 * @method string getThemeTitle()
 * @method string getThemeId()
 * @method Mage_Core_Model_Theme setAssignedStores(array $stores)
 * @method array getAssignedStores()
 * @method Mage_Core_Model_Theme addData(array $data)
 * @method Mage_Core_Model_Theme setParentId(int $id)
 * @method Mage_Core_Model_Theme setParentTheme($parentTheme)
 * @method Mage_Core_Model_Theme setPackageCode(string $packageCode)
 * @method Mage_Core_Model_Theme setThemeCode(string $themeCode)
 * @method Mage_Core_Model_Theme setPreviewImage(string $previewImage)
 * @method Mage_Core_Model_Theme setThemePath(string $themePath)
 * @method Mage_Core_Model_Theme setThemeVersion(string $themeVersion)
 * @method Mage_Core_Model_Theme setArea(string $area)
 * @method Mage_Core_Model_Theme setThemeTitle(string $themeTitle)
 * @method Mage_Core_Model_Theme setMagentoVersionFrom(string $versionFrom)
 * @method Mage_Core_Model_Theme setMagentoVersionTo(string $versionTo)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_Theme extends Mage_Core_Model_Abstract
{
    /**
     * Cache tag for empty theme
     */
    const CACHE_TAG_NO_THEME = 'NO_THEME';

    /**
     * Separator between theme_path elements
     */
    const PATH_SEPARATOR = '/';

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
     * Labels collection array
     *
     * @var array
     */
    protected $_labelsCollection;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Initialize dependencies
     *
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     * @param Mage_Core_Helper_Data $helper
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Resource_Theme $resource
     * @param Mage_Core_Model_Resource_Theme_Collection $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Theme_Factory $themeFactory,
        Mage_Core_Helper_Data $helper,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Resource_Theme $resource,
        Mage_Core_Model_Resource_Theme_Collection $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($eventDispatcher, $cacheManager, $resource, $resourceCollection, $data);
        $this->_objectManager = $objectManager;
        $this->_themeFactory = $themeFactory;
        $this->_helper = $helper;
        $this->_filesystem = $filesystem;
    }

    /**
     * Theme model initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Theme');
    }

    /**
     * Return custom css file
     *
     * @return Mage_Core_Model_Theme_Files
     */
    public function getCustomCssFile()
    {
        /** @var $cssFile Mage_Core_Model_Theme_Files_Css */
        $cssFile = $this->_objectManager->get('Mage_Core_Model_Theme_Files_Css');
        return $cssFile->getFileByTheme($this);
    }

    /**
     * Themes collection loaded from file system configurations
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    public function getCollectionFromFilesystem()
    {
        return $this->_objectManager->get('Mage_Core_Model_Theme_Collection');
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
        $validator = $this->_objectManager->get('Mage_Core_Model_Theme_Validator');
        if (!$validator->validate($this)) {
            $messages = $validator->getErrorMessages();
            Mage::throwException(implode(PHP_EOL, reset($messages)));
        }
        return $this;
    }

    /**
     * Check if theme is deletable
     *
     * @return bool
     */
    public function isDeletable()
    {
        return $this->isVirtual();
    }

    /**
     * Check if theme is editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->isVirtual();
    }

    /**
     * Check theme is existing in filesystem
     *
     * @return bool
     */
    public function isVirtual()
    {
        $collection = $this->getCollectionFromFilesystem()->addDefaultPattern('*')->getItems();
        return !($this->getThemePath() && isset($collection[$this->getFullPath()]));
    }

    /**
     * Check is theme has child themes
     *
     * @return bool
     */
    public function hasChildThemes()
    {
        $childThemes = $this->getCollection()->addFieldToFilter('parent_id', array('eq' => $this->getId()))->load();
        return (bool)count($childThemes);
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
            Mage::throwException($this->_helper->__('Theme isn\'t deletable.'));
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
     * Get parent theme model
     *
     * @return Mage_Core_Model_Theme|null
     */
    public function getParentTheme()
    {
        if ($this->hasData('parent_theme')) {
            return $this->getData('parent_theme');
        }

        $theme = null;
        if ($this->getParentId()) {
            $theme = $this->_themeFactory->create()->load($this->getParentId());
        }
        $this->setParentTheme($theme);
        return $theme;
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
    protected function _getPreviewImagePublishedRootDir()
    {
        /** @var $design Mage_Core_Model_Design_Package */
        $design = $this->_objectManager->get('Mage_Core_Model_Design_Package');
        $dirPath = $design->getPublicDir();
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($dirPath);
        $this->_filesystem->setWorkingDirectory($dirPath);
        return $dirPath;
    }

    /**
     * Get directory path for origin image
     *
     * @return string
     */
    public function getImagePathOrigin()
    {
        return $this->_getPreviewImagePublishedRootDir() . DIRECTORY_SEPARATOR . self::IMAGE_DIR_ORIGIN;
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    protected function _getImagePathPreview()
    {
        return $this->_getPreviewImagePublishedRootDir() . DIRECTORY_SEPARATOR . self::IMAGE_DIR_PREVIEW;
    }

    /**
     * Get preview image directory url
     *
     * @return string
     */
    public static function getPreviewImageDirectoryUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . Mage_Core_Model_Design_Package::PUBLIC_BASE_THEME_DIR . '/' . self::IMAGE_DIR_PREVIEW . '/';
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
            if ($this->getId() && !$this->isEditable()) {
                Mage::throwException($this->_helper->__('Theme isn\'t editable.'));
            }
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
        $this->setArea(Mage_Core_Model_App_Area::AREA_FRONTEND)->save();
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
            Mage::throwException($this->_helper->__('Uploaded image is not valid'));
        }
        $upload = new Varien_File_Uploader($scope);
        $upload->setAllowCreateFolders(true);
        $upload->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'));
        $upload->setAllowRenameFiles(true);
        $upload->setFilesDispersion(false);

        if (!$upload->save($this->getImagePathOrigin())) {
            Mage::throwException($this->_helper->__('Image can not be saved.'));
        }

        $fileName = $this->getImagePathOrigin() . DS . $upload->getUploadedFileName();
        $this->removePreviewImage()->createPreviewImage($fileName);
        $this->_filesystem->delete($fileName);
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
        $adapter = $this->_helper->getImageAdapterType();
        $image = new Varien_Image($imagePath, $adapter);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->resize(self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getMimeType());
        $image->save($this->_getImagePathPreview(), $imageName);

        $this->setPreviewImage($imageName);

        return $imageName;
    }

    /**
     * Create preview image copy
     *
     * @return Mage_Core_Model_Theme
     */
    public function createPreviewImageCopy()
    {
        $filePath = $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $this->getPreviewImage();
        $destinationFileName = Varien_File_Uploader::getNewFileName($filePath);
        $this->_filesystem->copy(
            $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $this->getPreviewImage(),
            $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $destinationFileName
        );
        $this->setPreviewImage($destinationFileName);
        return $this;
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
            $this->_filesystem->delete($this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $previewImage);
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
        return Mage::getDesign()->getViewFileUrl('Mage_Core::theme/default_preview.jpg');
    }

    /**
     * Return cache key for current theme
     *
     * @return string
     */
    public function getCacheKey()
    {
        if (!$this->getId()) {
            return self::CACHE_TAG_NO_THEME . $this->getThemePath();
        }

        return $this->getId() . $this->getThemePath();
    }

    /**
     * Retrieve theme full path which is used to distinguish themes if they are not in DB yet
     *
     * Alternative id looks like "<area>/<package_code>/<theme_code>".
     * Used as id in file-system theme collection
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->getArea() . '/' . $this->getThemePath();
    }

    /**
     * Check if the theme is compatible with Magento version
     *
     * @return bool
     */
    public function isThemeCompatible()
    {
        $magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, $this->getMagentoVersionFrom(), '>=')) {
            if ($this->getMagentoVersionTo() == '*'
                || version_compare($magentoVersion, $this->getMagentoVersionFrom(), '<=')
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the theme is compatible with Magento version and mark theme label if not compatible
     *
     * @return Mage_Core_Model_Theme
     */
    public function checkThemeCompatible()
    {
        if (!$this->isThemeCompatible()) {
            $this->setThemeTitle($this->_helper->__('%s (incompatible version)', $this->getThemeTitle()));
        }
        return $this;
    }

    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        if (!$this->_labelsCollection) {
            /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
            $themeCollection = $this->getCollection();
            $themeCollection->setOrder('theme_title', Varien_Data_Collection::SORT_ORDER_ASC)
                ->addAreaFilter(Mage_Core_Model_App_Area::AREA_FRONTEND)
                ->walk('checkThemeCompatible');
            $this->_labelsCollection = $themeCollection->toOptionArray();
        }
        $options = $this->_labelsCollection;
        if ($label) {
            array_unshift($options, array('value' => '', 'label' => $label));
        }
        return $options;
    }

    /**
     * Return labels collection for backend system configuration with empty value "No Theme"
     *
     * @return array
     */
    public function getLabelsCollectionForSystemConfiguration()
    {
        return $this->getLabelsCollection($this->_helper->__('-- No Theme --'));
    }

    /**
     * Clear data for clone
     */
    public function __clone()
    {
        $this->unsetData()->setOrigData();
    }
}
