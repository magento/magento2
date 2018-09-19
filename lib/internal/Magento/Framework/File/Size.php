<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento file size lib
 */
namespace Magento\Framework\File;

/**
 * @api
 */
class Size
{
    /**
     * Data size converter
     *
     * @var \Magento\Framework\Convert\DataSize
     */
    private $dataSize;

    /**
     * Maximum file size for MAX_FILE_SIZE attribute of a form
     *
     * @link http://www.php.net/manual/en/features.file-upload.post-method.php
     * @var integer
     */
    protected static $_maxFileSize = -1;

    /**
     * Get post max size
     *
     * @return string
     */
    public function getPostMaxSize()
    {
        return $this->_iniGet('post_max_size');
    }

    /**
     * Get upload max size
     *
     * @return string
     */
    public function getUploadMaxSize()
    {
        return $this->_iniGet('upload_max_filesize');
    }

    /**
     * Get max file size in megabytes
     *
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function getMaxFileSizeInMb($precision = 0, $mode = \PHP_ROUND_HALF_DOWN)
    {
        return $this->getFileSizeInMb($this->getMaxFileSize(), $precision, $mode);
    }

    /**
     * Get file size in megabytes
     *
     * @param int $fileSize
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function getFileSizeInMb($fileSize, $precision = 0, $mode = \PHP_ROUND_HALF_DOWN)
    {
        return round($fileSize / (1024 * 1024), $precision, $mode);
    }

    /**
     * Get the maximum file size of the a form in bytes
     *
     * @return integer
     */
    public function getMaxFileSize()
    {
        if (self::$_maxFileSize < 0) {
            $postMaxSize = $this->getDataSize()->convertSizeToBytes($this->getPostMaxSize());
            $uploadMaxSize = $this->getDataSize()->convertSizeToBytes($this->getUploadMaxSize());
            $min = max($postMaxSize, $uploadMaxSize);

            if ($postMaxSize > 0) {
                $min = min($min, $postMaxSize);
            }

            if ($uploadMaxSize > 0) {
                $min = min($min, $uploadMaxSize);
            }

            self::$_maxFileSize = $min;
        }

        return self::$_maxFileSize;
    }

    /**
     * Converts a ini setting to a integer value
     *
     * @deprecated 100.1.0 Please use \Magento\Framework\Convert\DataSize
     *
     * @param string $size
     * @return integer
     */
    public function convertSizeToInteger($size)
    {
        return $this->getDataSize()->convertSizeToBytes($size);
    }

    /**
     * Gets the value of a configuration option
     *
     * @link http://php.net/manual/en/function.ini-get.php
     * @param string $param The configuration option name
     * @return string
     */
    protected function _iniGet($param)
    {
        return trim(ini_get($param));
    }

    /**
     * The getter function to get the new dependency for real application code
     *
     * @return \Magento\Framework\Convert\DataSize
     *
     * @deprecated 100.1.0
     */
    private function getDataSize()
    {
        if ($this->dataSize === null) {
            $this->dataSize =
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Convert\DataSize::class);
        }

        return $this->dataSize;
    }
}
