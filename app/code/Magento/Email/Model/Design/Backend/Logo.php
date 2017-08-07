<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Logo as DesignLogo;

/**
 * Class \Magento\Email\Model\Design\Backend\Logo
 *
 * @since 2.2.0
 */
class Logo extends DesignLogo
{
    /**
     * The tail part of directory path for uploading
     */
    const UPLOAD_DIR = 'email/logo';

    /**
     * Upload max file size in kilobytes
     *
     * @var int
     * @since 2.2.0
     */
    protected $maxFileSize = 2048;

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     * @since 2.2.0
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
