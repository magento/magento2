<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Email\Model\Design\Backend;

use Magento\Theme\Model\Design\Backend\Logo as DesignLogo;

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
     */
    protected $maxFileSize = 2048;

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
