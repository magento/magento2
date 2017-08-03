<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\File;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * File upload class
 *
 * ATTENTION! This class must be used like abstract class and must added
 * validation by protected file extension list to extended class
 *
 * @api
 */
class Uploader
{
    /**
     * Uploaded file handle (copy of $_FILES[] element)
     *
     * @var array
     * @access protected
     */
    protected $_file;

    /**
     * Uploaded file mime type
     *
     * @var string
     * @access protected
     */
    protected $_fileMimeType;

    /**
     * Upload type. Used to right handle $_FILES array.
     *
     * @var \Magento\Framework\File\Uploader::SINGLE_STYLE|\Magento\Framework\File\Uploader::MULTIPLE_STYLE
     * @access protected
     */
    protected $_uploadType;

    /**
     * The name of uploaded file. By default it is original file name, but when
     * we will change file name, this variable will be changed too.
     *
     * @var string
     * @access protected
     */
    protected $_uploadedFileName;

    /**
     * The name of destination directory
     *
     * @var string
     * @access protected
     */
    protected $_uploadedFileDir;

    /**
     * If this variable is set to TRUE, our library will be able to automatically create
     * non-existent directories.
     *
     * @var bool
     * @access protected
     */
    protected $_allowCreateFolders = true;

    /**
     * If this variable is set to TRUE, uploaded file name will be changed if some file with the same
     * name already exists in the destination directory (if enabled).
     *
     * @var bool
     * @access protected
     */
    protected $_allowRenameFiles = false;

    /**
     * If this variable is set to TRUE, files dispertion will be supported.
     *
     * @var bool
     * @access protected
     */
    protected $_enableFilesDispersion = false;

    /**
     * This variable is used both with $_enableFilesDispersion == true
     * It helps to avoid problems after migrating from case-insensitive file system to case-insensitive
     * (e.g. NTFS->ext or ext->NTFS)
     *
     * @var bool
     * @access protected
     */
    protected $_caseInsensitiveFilenames = true;

    /**
     * @var string
     * @access protected
     */
    protected $_dispretionPath = null;

    /**
     * @var bool
     */
    protected $_fileExists = false;

    /**
     * @var null|string[]
     */
    protected $_allowedExtensions = null;

    /**
     * Validate callbacks storage
     *
     * @var array
     * @access protected
     */
    protected $_validateCallbacks = [];

    /**#@+
     * File upload type (multiple or single)
     */
    const SINGLE_STYLE = 0;

    const MULTIPLE_STYLE = 1;

    /**#@-*/

    /**
     * Temp file name empty code
     */
    const TMP_NAME_EMPTY = 666;

    /**
     * Max Image Width resolution in pixels. For image resizing on client side
     */
    const MAX_IMAGE_WIDTH = 1920;

    /**
     * Max Image Height resolution in pixels. For image resizing on client side
     */
    const MAX_IMAGE_HEIGHT = 1200;

    /**
     * Resulting of uploaded file
     *
     * @var array|bool      Array with file info keys: path, file. Result is
     *                      FALSE when file not uploaded
     */
    protected $_result;

    /**
     * Init upload
     *
     * @param string|array $fileId
     * @throws \Exception
     */
    public function __construct($fileId)
    {
        $this->_setUploadFileId($fileId);
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            throw new \Exception('The file was not uploaded.', $code);
        } else {
            $this->_fileExists = true;
        }
    }

    /**
     * After save logic
     *
     * @param  array $result
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave($result)
    {
        return $this;
    }

    /**
     * Used to save uploaded file into destination folder with
     * original or new file name (if specified)
     *
     * @param string $destinationFolder
     * @param string $newFileName
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($destinationFolder, $newFileName = null)
    {
        $this->_validateFile();
        $this->validateDestination($destinationFolder);

        $this->_result = false;
        $destinationFile = $destinationFolder;
        $fileName = isset($newFileName) ? $newFileName : $this->_file['name'];
        $fileName = self::getCorrectFileName($fileName);
        if ($this->_enableFilesDispersion) {
            $fileName = $this->correctFileNameCase($fileName);
            $this->setAllowCreateFolders(true);
            $this->_dispretionPath = self::getDispretionPath($fileName);
            $destinationFile .= $this->_dispretionPath;
            $this->_createDestinationFolder($destinationFile);
        }

        if ($this->_allowRenameFiles) {
            $fileName = self::getNewFileName(self::_addDirSeparator($destinationFile) . $fileName);
        }

        $destinationFile = self::_addDirSeparator($destinationFile) . $fileName;

        try {
            $this->_result = $this->_moveFile($this->_file['tmp_name'], $destinationFile);
        } catch (\Exception $e) {
            // if the file exists and we had an exception continue anyway
            if (file_exists($destinationFile)) {
                $this->_result = true;
            } else {
                throw $e;
            }
        }

        if ($this->_result) {
            if ($this->_enableFilesDispersion) {
                $fileName = str_replace('\\', '/', self::_addDirSeparator($this->_dispretionPath)) . $fileName;
            }
            $this->_uploadedFileName = $fileName;
            $this->_uploadedFileDir = $destinationFolder;
            $this->_result = $this->_file;
            $this->_result['path'] = $destinationFolder;
            $this->_result['file'] = $fileName;

            $this->_afterSave($this->_result);
        }

        return $this->_result;
    }

    /**
     * Validates destination directory to be writable
     *
     * @param string $destinationFolder
     * @return void
     * @throws \Exception
     * @since 2.1.0
     */
    private function validateDestination($destinationFolder)
    {
        if ($this->_allowCreateFolders) {
            $this->_createDestinationFolder($destinationFolder);
        }

        if (!is_writable($destinationFolder)) {
            throw new \Exception('Destination folder is not writable or does not exists.');
        }
    }

    /**
     * @param string $file
     * @return void
     *
     * @deprecated 2.0.6
     */
    protected function chmod($file)
    {
        chmod($file, 0777);
    }

    /**
     * Move files from TMP folder into destination folder
     *
     * @param string $tmpPath
     * @param string $destPath
     * @return bool|void
     */
    protected function _moveFile($tmpPath, $destPath)
    {
        if (is_uploaded_file($tmpPath)) {
            return move_uploaded_file($tmpPath, $destPath);
        } elseif (is_file($tmpPath)) {
            return rename($tmpPath, $destPath);
        }
    }

    /**
     * Validate file before save
     *
     * @return void
     * @throws \Exception
     */
    protected function _validateFile()
    {
        if ($this->_fileExists === false) {
            return;
        }

        //is file extension allowed
        if (!$this->checkAllowedExtension($this->getFileExtension())) {
            throw new \Exception('Disallowed file type.');
        }
        //run validate callbacks
        foreach ($this->_validateCallbacks as $params) {
            if (is_object($params['object'])
                && method_exists($params['object'], $params['method'])
                && is_callable([$params['object'], $params['method']])
            ) {
                $params['object']->{$params['method']}($this->_file['tmp_name']);
            }
        }
    }

    /**
     * Returns extension of the uploaded file
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->_fileExists ? pathinfo($this->_file['name'], PATHINFO_EXTENSION) : '';
    }

    /**
     * Add validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @param object $callbackObject
     * @param string $callbackMethod    Method name of $callbackObject. It must
     *                                  have interface (string $tmpFilePath)
     * @return \Magento\Framework\File\Uploader
     */
    public function addValidateCallback($callbackName, $callbackObject, $callbackMethod)
    {
        $this->_validateCallbacks[$callbackName] = ['object' => $callbackObject, 'method' => $callbackMethod];
        return $this;
    }

    /**
     * Delete validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @access public
     * @return \Magento\Framework\File\Uploader
     */
    public function removeValidateCallback($callbackName)
    {
        if (isset($this->_validateCallbacks[$callbackName])) {
            unset($this->_validateCallbacks[$callbackName]);
        }
        return $this;
    }

    /**
     * Correct filename with special chars and spaces
     *
     * @param string $fileName
     * @return string
     */
    public static function getCorrectFileName($fileName)
    {
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);
        $fileInfo = pathinfo($fileName);

        if (preg_match('/^_+$/', $fileInfo['filename'])) {
            $fileName = 'file.' . $fileInfo['extension'];
        }
        return $fileName;
    }

    /**
     * Convert filename to lowercase in case of case-insensitive file names
     *
     * @param string $fileName
     * @return string
     */
    public function correctFileNameCase($fileName)
    {
        if ($this->_caseInsensitiveFilenames) {
            return strtolower($fileName);
        }
        return $fileName;
    }

    /**
     * Add directory separator
     *
     * @param string $dir
     * @return string
     */
    protected static function _addDirSeparator($dir)
    {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        return $dir;
    }

    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param string[] $validTypes
     * @access public
     * @return bool
     */
    public function checkMimeType($validTypes = [])
    {
        if (count($validTypes) > 0) {
            if (!in_array($this->_getMimeType(), $validTypes)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a name of uploaded file
     *
     * @access public
     * @return string
     */
    public function getUploadedFileName()
    {
        return $this->_uploadedFileName;
    }

    /**
     * Used to set {@link _allowCreateFolders} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setAllowCreateFolders($flag)
    {
        $this->_allowCreateFolders = $flag;
        return $this;
    }

    /**
     * Used to set {@link _allowRenameFiles} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setAllowRenameFiles($flag)
    {
        $this->_allowRenameFiles = $flag;
        return $this;
    }

    /**
     * Used to set {@link _enableFilesDispersion} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setFilesDispersion($flag)
    {
        $this->_enableFilesDispersion = $flag;
        return $this;
    }

    /**
     * File names Case-sensitivity setter
     *
     * @param bool $flag
     * @return $this
     */
    public function setFilenamesCaseSensitivity($flag)
    {
        $this->_caseInsensitiveFilenames = $flag;
        return $this;
    }

    /**
     * Set allowed extensions
     *
     * @param string[] $extensions
     * @return $this
     */
    public function setAllowedExtensions($extensions = [])
    {
        foreach ((array)$extensions as $extension) {
            $this->_allowedExtensions[] = strtolower($extension);
        }
        return $this;
    }

    /**
     * Check if specified extension is allowed
     *
     * @param string $extension
     * @return boolean
     */
    public function checkAllowedExtension($extension)
    {
        if (!is_array($this->_allowedExtensions) || empty($this->_allowedExtensions)) {
            return true;
        }

        return in_array(strtolower($extension), $this->_allowedExtensions);
    }

    /**
     * Return file mime type
     *
     * @return string
     */
    private function _getMimeType()
    {
        return $this->_file['type'];
    }

    /**
     * Set upload field id
     *
     * @param string|array $fileId
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _setUploadFileId($fileId)
    {
        if (is_array($fileId)) {
            $this->_uploadType = self::MULTIPLE_STYLE;
            $this->_file = $fileId;
        } else {
            if (empty($_FILES)) {
                throw new \Exception('$_FILES array is empty');
            }

            preg_match("/^(.*?)\[(.*?)\]$/", $fileId, $file);

            if (count($file) > 0 && count($file[0]) > 0 && count($file[1]) > 0) {
                array_shift($file);
                $this->_uploadType = self::MULTIPLE_STYLE;

                $fileAttributes = $_FILES[$file[0]];
                $tmpVar = [];

                foreach ($fileAttributes as $attributeName => $attributeValue) {
                    $tmpVar[$attributeName] = $attributeValue[$file[1]];
                }

                $fileAttributes = $tmpVar;
                $this->_file = $fileAttributes;
            } elseif (count($fileId) > 0 && isset($_FILES[$fileId])) {
                $this->_uploadType = self::SINGLE_STYLE;
                $this->_file = $_FILES[$fileId];
            } elseif ($fileId == '') {
                throw new \Exception('Invalid parameter given. A valid $_FILES[] identifier is expected.');
            }
        }
    }

    /**
     * Create destination folder
     *
     * @param string $destinationFolder
     * @return \Magento\Framework\File\Uploader
     * @throws \Exception
     */
    private function _createDestinationFolder($destinationFolder)
    {
        if (!$destinationFolder) {
            return $this;
        }

        if (substr($destinationFolder, -1) == '/') {
            $destinationFolder = substr($destinationFolder, 0, -1);
        }

        if (!(@is_dir($destinationFolder)
            || @mkdir($destinationFolder, 0777, true)
        )) {
            throw new \Exception("Unable to create directory '{$destinationFolder}'.");
        }
        return $this;
    }

    /**
     * Get new file name if the same is already exists
     *
     * @param string $destinationFile
     * @return string
     */
    public static function getNewFileName($destinationFile)
    {
        $fileInfo = pathinfo($destinationFile);
        if (file_exists($destinationFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while (file_exists($fileInfo['dirname'] . '/' . $baseName)) {
                $baseName = $fileInfo['filename'] . '_' . $index . '.' . $fileInfo['extension'];
                $index++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }

    /**
     * Get dispertion path
     *
     * @param string $fileName
     * @return string
     */
    public static function getDispretionPath($fileName)
    {
        $char = 0;
        $dispertionPath = '';
        while ($char < 2 && $char < strlen($fileName)) {
            if (empty($dispertionPath)) {
                $dispertionPath = '/' . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            } else {
                $dispertionPath = self::_addDirSeparator(
                    $dispertionPath
                ) . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            }
            $char++;
        }
        return $dispertionPath;
    }
}
