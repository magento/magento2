<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Class Uploader specific to uploading images using services
 * @since 2.0.0
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * Avoid running the default constructor specific to FILE upload
     * @since 2.0.0
     */
    public function __construct()
    {
    }

    /**
     * Explicitly set the the file attributes instead of setting it via constructor
     *
     * @param array $fileAttributes
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function processFileAttributes($fileAttributes)
    {
        $this->_file = $fileAttributes;
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            throw new \Exception('File was not processed correctly.', $code);
        } else {
            $this->_fileExists = true;
        }
    }
}
