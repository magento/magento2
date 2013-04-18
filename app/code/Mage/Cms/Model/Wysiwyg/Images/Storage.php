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
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wysiwyg Images model
 *
 * @category    Mage
 * @package     Mage_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Cms_Model_Wysiwyg_Images_Storage extends Varien_Object
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';
    const THUMBS_DIRECTORY_NAME = '.thumbs';
    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'Mage_Cms::images/placeholder_thumbnail.jpg';

    /**
     * Config object
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Constructor
     *
     * @param Magento_Filesystem $filesystem
     * @param array $data
     */
    public function __construct(Magento_Filesystem $filesystem, array $data = array())
    {
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->setWorkingDirectory($this->getHelper()->getStorageRoot());
        parent::__construct($data);
    }

    /**
     * Return one-level child directories for specified path
     *
     * @param string $path Parent directory path
     * @return Varien_Data_Collection_Filesystem
     */
    public function getDirsCollection($path)
    {
        if (Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage()) {
            $subDirectories = Mage::getModel('Mage_Core_Model_File_Storage_Directory_Database')->getSubdirectories($path);
            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, DS) . DS . $directory['name'];
                $this->_filesystem->ensureDirectoryExists($fullPath, 0777, $path);
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        foreach ($this->getConfig()->dirs->exclude->children() as $dir) {
            $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][(string) $dir] = true;
        }
        // "include" section takes precedence and can revoke directory exclusion
        foreach ($this->getConfig()->dirs->include->children() as $dir) {
            unset($conditions['regexp'][(string) $dir], $conditions['plain'][(string) $dir]);
        }

        $regExp = $conditions['reg_exp'] ? ('~' . implode('|', array_keys($conditions['reg_exp'])) . '~i') : null;
        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        $storageRootLength = strlen($this->getHelper()->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode(DIRECTORY_SEPARATOR, substr($value->getFilename(), $storageRootLength));

            if (array_key_exists($rootChildParts[0], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        if (Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage()) {
            $files = Mage::getModel('Mage_Core_Model_File_Storage_Database')->getDirectoryFiles($path);

            $fileStorageModel = Mage::getModel('Mage_Core_Model_File_Storage_File');
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', Varien_Data_Collection::SORT_ORDER_ASC);

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }

        $helper = $this->getHelper();

        // prepare items
        foreach ($collection as $item) {
            $item->setId($helper->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($helper->getShortFilename($item->getBasename()));
            $item->setUrl($helper->getCurrentUrl() . $item->getBasename());

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if(! $thumbUrl) {
                    $thumbUrl = Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('*/*/thumbnail', array('file' => $item->getId()));
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = Mage::getDesign()->getViewFileUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }

    /**
     * Storage collection
     *
     * @param string $path Path to the directory
     * @return Varien_Data_Collection_Filesystem
     */
    public function getCollection($path = null)
    {
        $collection = Mage::getModel('Mage_Cms_Model_Wysiwyg_Images_Storage_Collection');
        if ($path !== null) {
            $collection->addTargetDir($path);
        }
        return $collection;
    }

    /**
     * Create new directory in storage
     *
     * @param string $name New directory name
     * @param string $path Parent directory path
     * @throws Mage_Core_Exception
     * @return array New directory info
     */
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            Mage::throwException(Mage::helper('Mage_Cms_Helper_Data')->__('Invalid folder name. Please, use alphanumeric characters, underscores and dashes.'));
        }
        if (!$this->_filesystem->isDirectory($path) || !$this->_filesystem->isWritable($path)) {
            $path = $this->getHelper()->getStorageRoot();
        }

        $newPath = $path . DS . $name;

        if ($this->_filesystem->isDirectory($newPath, $path)) {
            Mage::throwException(Mage::helper('Mage_Cms_Helper_Data')->__('A directory with the same name already exists. Please try another folder name.'));
        }

        $this->_filesystem->createDirectory($newPath);
        try {
            if (Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage()) {
                $relativePath = Mage::helper('Mage_Core_Helper_File_Storage_Database')->getMediaRelativePath($newPath);
                Mage::getModel('Mage_Core_Model_File_Storage_Directory_Database')->createRecursive($relativePath);
            }

            $result = array(
                'name'          => $name,
                'short_name'    => $this->getHelper()->getShortFilename($name),
                'path'          => $newPath,
                'id'            => $this->getHelper()->convertPathToId($newPath)
            );
            return $result;
        } Catch (Magento_Filesystem_Exception $e) {
            Mage::throwException(Mage::helper('Mage_Cms_Helper_Data')->__('Cannot create new directory.'));
        }
    }

    /**
     * Recursively delete directory from storage
     *
     * @param string $path Target dir
     * @return void
     */
    public function deleteDirectory($path)
    {
        // prevent accidental root directory deleting
        $rootCmp = rtrim($this->getHelper()->getStorageRoot(), DS);
        $pathCmp = rtrim($path, DS);

        if ($rootCmp == $pathCmp) {
            Mage::throwException(Mage::helper('Mage_Cms_Helper_Data')->__('Cannot delete root directory %s.', $path));
        }


        if (Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage()) {
            Mage::getModel('Mage_Core_Model_File_Storage_Directory_Database')->deleteDirectory($path);
        }
        try {
            $this->_filesystem->delete($path);
        } catch (Magento_Filesystem_Exception $e) {
            Mage::throwException(Mage::helper('Mage_Cms_Helper_Data')->__('Cannot delete directory %s.', $path));
        }

        if (strpos($pathCmp, $rootCmp) === 0) {
            $this->_filesystem->delete(
                $this->getThumbnailRoot() . DS . ltrim(substr($pathCmp, strlen($rootCmp)), '\\/')
            );
        }
    }

    /**
     * Delete file (and its thumbnail if exists) from storage
     *
     * @param string $target File path to be deleted
     * @return Mage_Cms_Model_Wysiwyg_Images_Storage
     */
    public function deleteFile($target)
    {
        if ($this->_filesystem->isFile($target)) {
            $this->_filesystem->delete($target);
        }
        Mage::helper('Mage_Core_Helper_File_Storage_Database')->deleteFile($target);

        $thumb = $this->getThumbnailPath($target, true);
        if ($thumb) {
            if ($this->_filesystem->isFile($thumb)) {
                $this->_filesystem->delete($thumb);
            }
            Mage::helper('Mage_Core_Helper_File_Storage_Database')->deleteFile($thumb);
        }
        return $this;
    }


    /**
     * Upload and resize new file
     *
     * @param string $targetPath Target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @throws Mage_Core_Exception
     * @return array File info Array
     */
    public function uploadFile($targetPath, $type = null)
    {
        $uploader = new Mage_Core_Model_File_Uploader('image');
        if ($allowed = $this->getAllowedExtensions($type)) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            Mage::throwException( Mage::helper('Mage_Cms_Helper_Data')->__('Cannot upload file.') );
        }

        // create thumbnail
        $this->resizeFile($targetPath . DS . $uploader->getUploadedFileName(), true);

        $result['cookie'] = array(
            'name'     => session_name(),
            'value'    => $this->getSession()->getSessionId(),
            'lifetime' => $this->getSession()->getCookieLifetime(),
            'path'     => $this->getSession()->getCookiePath(),
            'domain'   => $this->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Thumbnail path getter
     *
     * @param  string $filePath original file path
     * @param  boolean $checkFile OPTIONAL is it necessary to check file availability
     * @return string | false
     */
    public function getThumbnailPath($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->getHelper()->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbPath = $this->getThumbnailRoot() . DS . substr($filePath, strlen($mediaRootDir));

            if (!$checkFile || $this->_filesystem->isReadable($thumbPath)) {
                return $thumbPath;
            }
        }

        return false;
    }

    /**
     * Thumbnail URL getter
     *
     * @param  string $filePath original file path
     * @param  boolean $checkFile OPTIONAL is it necessary to check file availability
     * @return string | false
     */
    public function getThumbnailUrl($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->getHelper()->getStorageRoot();

        if (strpos($filePath, $mediaRootDir) === 0) {
            $thumbSuffix = self::THUMBS_DIRECTORY_NAME . DS . substr($filePath, strlen($mediaRootDir));

            if (! $checkFile || $this->_filesystem->isReadable($mediaRootDir . $thumbSuffix)) {
                $randomIndex = '?rand=' . time();
                return str_replace('\\', '/', $this->getHelper()->getBaseUrl() . $thumbSuffix) . $randomIndex;
            }
        }

        return false;
    }

    /**
     * Create thumbnail for image and save it to thumbnails directory
     *
     * @param string $source Image path to be resized
     * @param bool $keepRation Keep aspect ratio or not
     * @return bool|string Resized filepath or false if errors were occurred
     */
    public function resizeFile($source, $keepRation = true)
    {
        if (!$this->_filesystem->isFile($source)
            || !$this->_filesystem->isReadable($source)) {
            return false;
        }

        $targetDir = $this->getThumbsPath($source);
        if (!$this->_filesystem->isWritable($targetDir)) {
            $this->_filesystem->createDirectory($targetDir);
        }
        if (!$this->_filesystem->isWritable($targetDir)) {
            return false;
        }
        $adapter = Mage::helper('Mage_Core_Helper_Data')->getImageAdapterType();
        $image = Varien_Image_Adapter::factory($adapter);
        $image->open($source);
        $width = $this->getConfigData('resize_width');
        $height = $this->getConfigData('resize_height');
        $image->keepAspectRatio($keepRation);
        $image->resize($width, $height);
        $dest = $targetDir . DS . pathinfo($source, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_filesystem->isFile($dest)) {
            return $dest;
        }
        return false;
    }

    /**
     * Resize images on the fly in controller action
     *
     * @param string File basename
     * @return bool|string Thumbnail path or false for errors
     */
    public function resizeOnTheFly($filename)
    {
        $path = $this->getSession()->getCurrentPath();
        if (!$path) {
            $path = $this->getHelper()->getCurrentPath();
        }
        return $this->resizeFile($path . DS . $filename);
    }

    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param string $filePath Path to the file
     * @return string
     */
    public function getThumbsPath($filePath = false)
    {
        $mediaRootDir = Mage::getBaseDir(Mage_Core_Model_Dir::MEDIA);
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= DS . dirname(substr($filePath, strlen($mediaRootDir)));
        }

        return $thumbnailDir;
    }

    /**
     * Media Storage Helper getter
     * @return Mage_Cms_Helper_Wysiwyg_Images
     */
    public function getHelper()
    {
        return Mage::helper('Mage_Cms_Helper_Wysiwyg_Images');
    }

    /**
     * Storage session
     *
     * @return Mage_Adminhtml_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session');
    }

    /**
     * Config object getter
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if (! $this->_config) {
            $this->_config = Mage::getConfig()->getNode('cms/browser', 'adminhtml');
        }

        return $this->_config;
    }

    /**
     * Config object as array getter
     *
     * @return array
     */
    public function getConfigAsArray()
    {
        if (! $this->_configAsArray) {
            $this->_configAsArray = $this->getConfig()->asCanonicalArray();
        }

        return $this->_configAsArray;
    }

    /**
     * Wysiwyg Config reader
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigData($key, $default=false)
    {
        $configArray = $this->getConfigAsArray();
        $key = (string) $key;

        return array_key_exists($key, $configArray) ? $configArray[$key] : $default;
    }

    /**
     * Prepare allowed_extensions config settings
     *
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    public function getAllowedExtensions($type = null)
    {
        $extensions = $this->getConfigData('extensions');

        if (is_string($type) && array_key_exists("{$type}_allowed", $extensions)) {
            $allowed = $extensions["{$type}_allowed"];
        } else {
            $allowed = $extensions['allowed'];
        }

        return array_keys(array_filter($allowed));
    }

    /**
     * Thumbnail root directory getter
     *
     * @return string
     */
    public function getThumbnailRoot()
    {
        return $this->getHelper()->getStorageRoot() . self::THUMBS_DIRECTORY_NAME;
    }

    /**
     * Simple way to check whether file is image or not based on extension
     *
     * @param string $filename
     * @return bool
     */
    public function isImage($filename)
    {
        if (!$this->hasData('_image_extensions')) {
            $this->setData('_image_extensions', $this->getAllowedExtensions('image'));
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $this->_getData('_image_extensions'));
    }
}
