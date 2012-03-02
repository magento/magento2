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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * File uploader for API
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Link_Api_Uploader extends Mage_Core_Model_File_Uploader
{
    /**
     * Filename prefix
     *
     * @var string
     */
    protected $_filePrefix = 'Api';

    /**
     * Default file type
     */
    const DEFAULT_FILE_TYPE = 'application/octet-stream';

    /**
     * Check if the uploaded file exists
     *
     * @throws Exception
     * @param array $file
     */
    public function __construct($file)
    {
        $this->_setUploadFile($file);
        if( !file_exists($this->_file['tmp_name']) ) {
            throw new Exception('', 'file_not_uploaded');
        } else {
            $this->_fileExists = true;
        }
    }

    /**
     * Sets uploaded file info and decodes the file
     *
     * @throws Exception
     * @param array $fileInfo
     * @return void
     */
    private function _setUploadFile($fileInfo)
    {
        if (!is_array($fileInfo)) {
            throw new Exception('', 'file_data_not_correct');
        }

        $this->_file = $this->_decodeFile($fileInfo);
        $this->_uploadType = self::SINGLE_STYLE;
    }

    /**
     * Decode uploaded file base64 encoded content
     *
     * @param array $fileInfo
     * @return array
     */
    private function _decodeFile($fileInfo)
    {
        $tmpFileName = $this->_getTmpFilePath();

        $file = new Varien_Io_File();
        $file->open(array('path' => sys_get_temp_dir()));
        $file->streamOpen($tmpFileName);
        $file->streamWrite(base64_decode($fileInfo['base64_content']));
        $file->streamClose();

        return array(
            'name' => $fileInfo['name'],
            'type' => isset($fileInfo['type'])? $fileInfo['type'] : self::DEFAULT_FILE_TYPE,
            'tmp_name' => $tmpFileName,
            'error' => 0,
            'size' => filesize($tmpFileName)
        );
    }

    /**
     * Generate temporary file name
     *
     * @return string
     */
    private function _getTmpFilePath()
    {
        return tempnam(sys_get_temp_dir(), $this->_filePrefix);

    }

    /**
     * Moves a file
     *
     * @param string $sourceFile
     * @param string $destinationFile
     * @return bool
     */
    protected function _moveFile($sourceFile, $destinationFile)
    {
        return rename($sourceFile, $destinationFile);
    }

}
