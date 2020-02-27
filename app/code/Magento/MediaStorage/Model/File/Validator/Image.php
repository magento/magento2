<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\File\Validator;

use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Image validator
 */
class Image extends \Zend_Validate_Abstract
{
    /**
     * @var array
     */
    private $imageMimeTypes = [
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
    ];

    /**
     * @var Mime
     */
    private $fileMime;

    /**
     * @var File
     */
    private $file;

    /**
     * @param Mime $fileMime
     * @param File $file
     */
    public function __construct(
        Mime $fileMime,
        File $file
    ) {
        $this->fileMime = $fileMime;
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function isValid($filePath): bool
    {
        $fileMimeType = $this->fileMime->getMimeType($filePath);
        $isValid = true;

        if (in_array($fileMimeType, $this->imageMimeTypes)) {
            try {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $image = imagecreatefromstring($this->file->fileGetContents($filePath));

                $isValid = $image ? true : false;
            } catch (\Exception $e) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}
