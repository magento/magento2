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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product option file type
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Option\Type;

class File extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{
    /**
     * Url for custom option download controller
     * @var string
     */
    protected $_customOptionDownloadUrl = 'sales/download/downloadCustomOption';

    /**
     * @var mixed
     */
    protected $_formattedOptionValue = null;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\File\Size
     */
    protected $_fileSize;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Dir
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Url
     *
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * Item option factory
     *
     * @var \Magento\Sales\Model\Quote\Item\OptionFactory
     */
    protected $_itemOptionFactory;

    /**
     * Construct
     *
     * @param \Magento\Sales\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Magento\UrlInterface $url
     * @param \Magento\App\Dir $dir
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\File\Size $fileSize
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\UrlInterface $url,
        \Magento\App\Dir $dir,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Filesystem $filesystem,
        \Magento\File\Size $fileSize,
        $data = array()
    ) {
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->_url = $url;
        $this->_dir = $dir;
        $this->_coreData = $coreData;
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->_filesystem = $filesystem;
        $this->_fileSize = $fileSize;
        $this->_data = $data;
        parent::__construct($checkoutSession, $coreStoreConfig, $data);
    }


    /**
     * Flag to indicate that custom option has own customized output (blocks, native html etc.)
     *
     * @return boolean
     */
    public function isCustomizedView()
    {
        return true;
    }

    /**
     * Return option html
     *
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedView($optionInfo)
    {
        try {
            if (isset($optionInfo['option_value'])) {
                return $this->_getOptionHtml($optionInfo['option_value']);
            } elseif (isset($optionInfo['value'])) {
                return $optionInfo['value'];
            }
        } catch (\Exception $e) {
            return $optionInfo['value'];
        }
    }

    /**
     * Returns additional params for processing options
     *
     * @return \Magento\Object
     */
    protected function _getProcessingParams()
    {
        $buyRequest = $this->getRequest();
        $params = $buyRequest->getData('_processing_params');
        /*
         * Notice check for params to be \Magento\Object - by using object we protect from
         * params being forged and contain data from user frontend input
         */
        if ($params instanceof \Magento\Object) {
            return $params;
        }
        return new \Magento\Object();
    }

    /**
     * Returns file info array if we need to get file from already existing file.
     * Or returns null, if we need to get file from uploaded array.
     *
     * @return null|array
     */
    protected function _getCurrentConfigFileInfo()
    {
        $option = $this->getOption();
        $optionId = $option->getId();
        $processingParams = $this->_getProcessingParams();
        $buyRequest = $this->getRequest();

        // Check maybe restore file from config requested
        $optionActionKey = 'options_' . $optionId . '_file_action';
        if ($buyRequest->getData($optionActionKey) == 'save_old') {
            $fileInfo = array();
            $currentConfig = $processingParams->getCurrentConfig();
            if ($currentConfig) {
                $fileInfo = $currentConfig->getData('options/' . $optionId);
            }
            return $fileInfo;
        }
        return null;
    }

    /**
     * Validate user input for option
     *
     * @throws \Magento\Core\Exception
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    public function validateUserValue($values)
    {
        $this->_checkoutSession->setUseNotice(false);

        $this->setIsValid(true);
        $option = $this->getOption();

        /*
         * Check whether we receive uploaded file or restore file by: reorder/edit configuration or
         * previous configuration with no newly uploaded file
         */
        $fileInfo = null;
        if (isset($values[$option->getId()]) && is_array($values[$option->getId()])) {
            // Legacy style, file info comes in array with option id index
            $fileInfo = $values[$option->getId()];
        } else {
            /*
             * New recommended style - file info comes in request processing parameters and we
             * sure that this file info originates from Magento, not from manually formed POST request
             */
            $fileInfo = $this->_getCurrentConfigFileInfo();
        }
        if ($fileInfo !== null) {
            if (is_array($fileInfo) && $this->_validateFile($fileInfo)) {
                $value = $fileInfo;
            } else {
                $value = null;
            }
            $this->setUserValue($value);
            return $this;
        }

        // Process new uploaded file
        try {
            $this->_validateUploadedFile();
        } catch (\Exception $e) {
            if ($this->getSkipCheckRequiredOption()) {
                $this->setUserValue(null);
                return $this;
            } else {
                throw new \Magento\Core\Exception($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Validate uploaded file
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    protected function _validateUploadedFile()
    {
        $option = $this->getOption();
        $processingParams = $this->_getProcessingParams();

        /**
         * Upload init
         */
        $upload = new \Zend_File_Transfer_Adapter_Http();
        $file = $processingParams->getFilesPrefix() . 'options_' . $option->getId() . '_file';
        $maxFileSize = $this->getFileSizeService()->getMaxFileSize();
        try {
            $runValidation = $option->getIsRequire() || $upload->isUploaded($file);
            if (!$runValidation) {
                $this->setUserValue(null);
                return $this;
            }

            $fileInfo = $upload->getFileInfo($file);
            $fileInfo = $fileInfo[$file];
            $fileInfo['title'] = $fileInfo['name'];

        } catch (\Exception $e) {
            // when file exceeds the upload_max_filesize, $_FILES is empty
            if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $maxFileSize) {
                $this->setIsValid(false);
                $value = $this->getFileSizeService()->getMaxFileSizeInMb();
                throw new \Magento\Core\Exception(
                    __("The file you uploaded is larger than %1 Megabytes allowed by server", $value)
                );
            } else {
                switch ($this->getProcessMode())
                {
                    case \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL:
                        throw new \Magento\Core\Exception(
                            __('Please specify the product\'s required option(s).')
                        );
                        break;
                    default:
                        $this->setUserValue(null);
                        break;
                }
                return $this;
            }
        }

        /**
         * Option Validations
         */

        // Image dimensions
        $_dimentions = array();
        if ($option->getImageSizeX() > 0) {
            $_dimentions['maxwidth'] = $option->getImageSizeX();
        }
        if ($option->getImageSizeY() > 0) {
            $_dimentions['maxheight'] = $option->getImageSizeY();
        }
        if (count($_dimentions) > 0) {
            $upload->addValidator('ImageSize', false, $_dimentions);
        }

        // File extension
        $_allowed = $this->_parseExtensionsString($option->getFileExtension());
        if ($_allowed !== null) {
            $upload->addValidator('Extension', false, $_allowed);
        } else {
            $_forbidden = $this->_parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($_forbidden !== null) {
                $upload->addValidator('ExcludeExtension', false, $_forbidden);
            }
        }

        // Maximum filesize
        $upload->addValidator('FilesSize', false, array('max' => $maxFileSize));

        /**
         * Upload process
         */

        $this->_initFilesystem();

        if ($upload->isUploaded($file) && $upload->isValid($file)) {

            $extension = pathinfo(strtolower($fileInfo['name']), PATHINFO_EXTENSION);

            $fileName = \Magento\Core\Model\File\Uploader::getCorrectFileName($fileInfo['name']);
            $dispersion = \Magento\Core\Model\File\Uploader::getDispretionPath($fileName);

            $filePath = $dispersion;
            $dirName = dirname($fileInfo['tmp_name']);
            $fileHash = md5($this->_filesystem->read($fileInfo['tmp_name'], $dirName));
            $filePath .= DS . $fileHash . '.' . $extension;
            $fileFullPath = $this->getQuoteTargetDir() . $filePath;

            $upload->addFilter('Rename', array(
                'target' => $fileFullPath,
                'overwrite' => true
            ));

            $this->getProduct()->getTypeInstance()->addFileQueue(array(
                'operation' => 'receive_uploaded_file',
                'src_name'  => $file,
                'dst_name'  => $fileFullPath,
                'uploader'  => $upload,
                'option'    => $this,
            ));

            $_width = 0;
            $_height = 0;
            if ($this->_filesystem->isReadable($fileInfo['tmp_name'], $dirName)) {
                $_imageSize = getimagesize($fileInfo['tmp_name']);
                if ($_imageSize) {
                    $_width = $_imageSize[0];
                    $_height = $_imageSize[1];
                }
            }

            $this->setUserValue(array(
                'type'          => $fileInfo['type'],
                'title'         => $fileInfo['name'],
                'quote_path'    => $this->getQuoteTargetDir(true) . $filePath,
                'order_path'    => $this->getOrderTargetDir(true) . $filePath,
                'fullpath'      => $fileFullPath,
                'size'          => $fileInfo['size'],
                'width'         => $_width,
                'height'        => $_height,
                'secret_key'    => substr($fileHash, 0, 20),
            ));

        } elseif ($upload->getErrors()) {
            $errors = $this->_getValidatorErrors($upload->getErrors(), $fileInfo);

            if (count($errors) > 0) {
                $this->setIsValid(false);
                throw new \Magento\Core\Exception( implode("\n", $errors) );
            }
        } else {
            $this->setIsValid(false);
            throw new \Magento\Core\Exception(__('Please specify the product required option(s).'));
        }
        return $this;
    }

    /**
     * Validate file
     *
     * @throws \Magento\Core\Exception
     * @param array $optionValue
     * @return \Magento\Catalog\Model\Product\Option\Type\DefaultType
     */
    protected function _validateFile($optionValue)
    {
        $option = $this->getOption();
        /**
         * @see \Magento\Catalog\Model\Product\Option\Type\File::_validateUploadFile()
         *              There setUserValue() sets correct fileFullPath only for
         *              quote_path. So we must form both full paths manually and
         *              check them.
         */
        $checkPaths = array();
        if (isset($optionValue['quote_path'])) {
            $checkPaths[] = $this->_dir->getDir() . $optionValue['quote_path'];
        }
        if (isset($optionValue['order_path']) && !$this->getUseQuotePath()) {
            $checkPaths[] = $this->_dir->getDir() . $optionValue['order_path'];
        }

        $fileFullPath = null;
        foreach ($checkPaths as $path) {
            if (!$this->_filesystem->isFile($path)) {
                if (!$this->_coreFileStorageDatabase->saveFileToFilesystem($fileFullPath)) {
                    continue;
                }
            }
            $fileFullPath = $path;
            break;
        }

        if ($fileFullPath === null) {
            return false;
        }

        $validatorChain = new \Zend_Validate();

        $_dimentions = array();

        if ($option->getImageSizeX() > 0) {
            $_dimentions['maxwidth'] = $option->getImageSizeX();
        }
        if ($option->getImageSizeY() > 0) {
            $_dimentions['maxheight'] = $option->getImageSizeY();
        }
        if (count($_dimentions) > 0 && !$this->_isImage($fileFullPath)) {
            return false;
        }
        if (count($_dimentions) > 0) {
            $validatorChain->addValidator(
                new \Zend_Validate_File_ImageSize($_dimentions)
            );
        }

        // File extension
        $_allowed = $this->_parseExtensionsString($option->getFileExtension());
        if ($_allowed !== null) {
            $validatorChain->addValidator(new \Zend_Validate_File_Extension($_allowed));
        } else {
            $_forbidden = $this->_parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($_forbidden !== null) {
                $validatorChain->addValidator(new \Zend_Validate_File_ExcludeExtension($_forbidden));
            }
        }

        // Maximum file size
        $maxFileSize = $this->getFileSizeService()->getMaxFileSize();
        $validatorChain->addValidator(
                new \Zend_Validate_File_FilesSize(array('max' => $maxFileSize))
        );


        if ($validatorChain->isValid($fileFullPath)) {
            $ok = $this->_filesystem->isReadable($fileFullPath)
                && isset($optionValue['secret_key'])
                && substr(md5($this->_filesystem->read($fileFullPath)), 0, 20) == $optionValue['secret_key'];

            return $ok;
        } elseif ($validatorChain->getErrors()) {
            $errors = $this->_getValidatorErrors($validatorChain->getErrors(), $optionValue);

            if (count($errors) > 0) {
                $this->setIsValid(false);
                throw new \Magento\Core\Exception( implode("\n", $errors) );
            }
        } else {
            $this->setIsValid(false);
            throw new \Magento\Core\Exception(__('Please specify the product required option(s).'));
        }
    }

    /**
     * Get Error messages for validator Errors
     *
     * @param array $errors Array of validation failure message codes @see \Zend_Validate::getErrors()
     * @param array $fileInfo File info
     * @return array Array of error messages
     */
    protected function _getValidatorErrors($errors, $fileInfo)
    {
        $option = $this->getOption();
        $result = array();
        foreach ($errors as $errorCode) {
            if ($errorCode == \Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION) {
                $result[] = __("The file '%1' for '%2' has an invalid extension.", $fileInfo['title'], $option->getTitle());
            } elseif ($errorCode == \Zend_Validate_File_Extension::FALSE_EXTENSION) {
                $result[] = __("The file '%1' for '%2' has an invalid extension.", $fileInfo['title'], $option->getTitle());
            } elseif ($errorCode == \Zend_Validate_File_ImageSize::WIDTH_TOO_BIG
                || $errorCode == \Zend_Validate_File_ImageSize::HEIGHT_TOO_BIG) {
                $result[] = __(
                    "Maximum allowed image size for '%1' is %2x%3 px.",
                    $option->getTitle(),
                    $option->getImageSizeX(),
                    $option->getImageSizeY()
                );
            } elseif ($errorCode == \Zend_Validate_File_FilesSize::TOO_BIG) {
                $maxFileSize = $this->getFileSizeService()->getMaxFileSizeInMb();
                $result[] = __("The file '%1' you uploaded is larger than the %2 megabytes allowed by our server.", $fileInfo['title'], $maxFileSize);
            }
        }
        return $result;
    }

    /**
     * Prepare option value for cart
     *
     * @return mixed Prepared option value
     */
    public function prepareForCart()
    {
        $option = $this->getOption();
        $optionId = $option->getId();
        $buyRequest = $this->getRequest();

        // Prepare value and fill buyRequest with option
        $requestOptions = $buyRequest->getOptions();
        if ($this->getIsValid() && $this->getUserValue() !== null) {
            $value = $this->getUserValue();

            // Save option in request, because we have no $_FILES['options']
            $requestOptions[$this->getOption()->getId()] = $value;
            $result = serialize($value);
        } else {
            /*
             * Clear option info from request, so it won't be stored in our db upon
             * unsuccessful validation. Otherwise some bad file data can happen in buyRequest
             * and be used later in reorders and reconfigurations.
             */
            if (is_array($requestOptions)) {
                unset($requestOptions[$this->getOption()->getId()]);
            }
            $result = null;
        }
        $buyRequest->setOptions($requestOptions);

        // Clear action key from buy request - we won't need it anymore
        $optionActionKey = 'options_' . $optionId . '_file_action';
        $buyRequest->unsetData($optionActionKey);

        return $result;
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            try {
                $value = unserialize($optionValue);

                $customOptionUrlParams = $this->getCustomOptionUrlParams()
                    ? $this->getCustomOptionUrlParams()
                    : array(
                        'id'  => $this->getConfigurationItemOption()->getId(),
                        'key' => $value['secret_key']
                    );

                $value['url'] = array('route' => $this->_customOptionDownloadUrl, 'params' => $customOptionUrlParams);

                $this->_formattedOptionValue = $this->_getOptionHtml($value);
                $this->getConfigurationItemOption()->setValue(serialize($value));
                return $this->_formattedOptionValue;
            } catch (\Exception $e) {
                return $optionValue;
            }
        }
        return $this->_formattedOptionValue;
    }

    /**
     * Format File option html
     *
     * @param string|array $optionValue Serialized string of option data or its data array
     * @return string
     */
    protected function _getOptionHtml($optionValue)
    {
        $value = $this->_unserializeValue($optionValue);
        try {
            if (isset($value) && isset($value['width']) && isset($value['height'])
                && $value['width'] > 0 && $value['height'] > 0
            ) {
                $sizes = $value['width'] . ' x ' . $value['height'] . ' ' . __('px.');
            } else {
                $sizes = '';
            }

            $urlRoute = !empty($value['url']['route']) ? $value['url']['route'] : '';
            $urlParams = !empty($value['url']['params']) ? $value['url']['params'] : '';
            $title = !empty($value['title']) ? $value['title'] : '';

            return sprintf('<a href="%s" target="_blank">%s</a> %s',
                $this->_getOptionDownloadUrl($urlRoute, $urlParams),
                $this->_coreData->escapeHtml($title),
                $sizes
            );
        } catch (\Exception $e) {
            throw new \Magento\Core\Exception(__("The file options format is not valid."));
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_array($value)) {
            return $value;
        } elseif (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return array();
        }
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getPrintableOptionValue($optionValue)
    {
        return strip_tags($this->getFormattedOptionValue($optionValue));
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getEditableOptionValue($optionValue)
    {
        try {
            $value = unserialize($optionValue);
            return sprintf('%s [%d]',
                $this->_coreData->escapeHtml($value['title']),
                $this->getConfigurationItemOption()->getId()
            );

        } catch (\Exception $e) {
            return $optionValue;
        }
    }

    /**
     * Parse user input value and return cart prepared value
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        // search quote item option Id in option value
        if (preg_match('/\[([0-9]+)\]/', $optionValue, $matches)) {
            $confItemOptionId = $matches[1];
            $option = $this->_itemOptionFactory->create()->load($confItemOptionId);
            try {
                unserialize($option->getValue());
                return $option->getValue();
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return mixed
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        try {
            $result = unserialize($optionValue);
            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Quote item to order item copy process
     *
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    public function copyQuoteToOrder()
    {
        $quoteOption = $this->getConfigurationItemOption();
        try {
            $value = unserialize($quoteOption->getValue());
            if (!isset($value['quote_path'])) {
                throw new \Exception();
            }
            $quoteFileFullPath = $this->_dir->getDir() . $value['quote_path'];
            if (!$this->_filesystem->isFile($quoteFileFullPath)
                || !$this->_filesystem->isReadable($quoteFileFullPath)
            ) {
                throw new \Exception();
            }
            $orderFileFullPath = $this->_dir->getDir() . $value['order_path'];
            $dir = pathinfo($orderFileFullPath, PATHINFO_DIRNAME);
            $this->_createWritableDir($dir);
            $this->_coreFileStorageDatabase->copyFile($quoteFileFullPath, $orderFileFullPath);
            $this->_filesystem->copy($quoteFileFullPath, $orderFileFullPath);
        } catch (\Exception $e) {
            return $this;
        }
        return $this;
    }

    /**
     * Main Destination directory
     *
     * @param boolean $relative If true - returns relative path to the webroot
     * @return string
     */
    public function getTargetDir($relative = false)
    {
        $fullPath = $this->_dir->getDir(\Magento\App\Dir::MEDIA) . DS . 'custom_options';
        return $relative ? str_replace($this->_dir->getDir(), '', $fullPath) : $fullPath;
    }

    /**
     * Quote items destination directory
     *
     * @param boolean $relative If true - returns relative path to the webroot
     * @return string
     */
    public function getQuoteTargetDir($relative = false)
    {
        return $this->getTargetDir($relative) . DS . 'quote';
    }

    /**
     * Order items destination directory
     *
     * @param boolean $relative If true - returns relative path to the webroot
     * @return string
     */
    public function getOrderTargetDir($relative = false)
    {
        return $this->getTargetDir($relative) . DS . 'order';
    }

    /**
     * Set url to custom option download controller
     *
     * @param string $url
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    public function setCustomOptionDownloadUrl($url)
    {
        $this->_customOptionDownloadUrl = $url;
        return $this;
    }

    /**
     * Directory structure initializing
     */
    protected function _initFilesystem()
    {
        $this->_createWritableDir($this->getTargetDir());
        $this->_createWritableDir($this->getQuoteTargetDir());
        $this->_createWritableDir($this->getOrderTargetDir());

        // Directory listing and hotlink secure
        if (!$this->_filesystem->isFile($this->getTargetDir() . DS . '.htaccess')) {
            $stream = $this->_filesystem->createStream($this->getTargetDir() . DS . '.htaccess');
            $stream->open('w+');
            $stream->write("Order deny,allow\nDeny from all");
            $stream->close();
        }
    }

    /**
     * Create Writable directory if it doesn't exist
     *
     * @param string Absolute directory path
     * @return void
     * @throws \Magento\Core\Exception
     */
    protected function _createWritableDir($path)
    {
        try {
            if (!$this->_filesystem->isWritable($path)) {
                $this->_filesystem->createDirectory($path, 0777);
            }
        } catch (\Magento\Filesystem\Exception $e) {
            throw new \Magento\Core\Exception(__("Cannot create writable directory '%1'.", $path));
        }
    }

    /**
     * Return URL for option file download
     *
     * @param $route
     * @param $params
     * @return string
     */
    protected function _getOptionDownloadUrl($route, $params)
    {
        return $this->_url->getUrl($route, $params);
    }

    /**
     * Parse file extensions string with various separators
     *
     * @param string $extensions String to parse
     * @return array|null
     */
    protected function _parseExtensionsString($extensions)
    {
        preg_match_all('/[a-z0-9]+/si', strtolower($extensions), $matches);
        if (isset($matches[0]) && is_array($matches[0]) && count($matches[0]) > 0) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Simple check if file is image
     *
     * @param array|string $fileInfo - either file data from \Zend_File_Transfer or file path
     * @return boolean
     */
    protected function _isImage($fileInfo)
    {
        // Maybe array with file info came in
        if (is_array($fileInfo)) {
            return strstr($fileInfo['type'], 'image/');
        }

        // File path came in - check the physical file
        if (!$this->_filesystem->isReadable($fileInfo)) {
            return false;
        }
        $imageInfo = getimagesize($fileInfo);
        if (!$imageInfo) {
            return false;
        }
        return true;
    }

    /**
     * Get file storage helper
     *
     * @return \Magento\File\Size
     */
    public function getFileSizeService()
    {
        return $this->_fileSize;
    }
}
