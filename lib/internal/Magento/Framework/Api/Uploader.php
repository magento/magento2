<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Class Uploader specific to uploading images using services
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    public function __construct($fileAttributes)
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
