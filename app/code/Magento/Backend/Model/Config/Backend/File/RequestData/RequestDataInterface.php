<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Request data interface
 */
namespace Magento\Backend\Model\Config\Backend\File\RequestData;

interface RequestDataInterface
{
    /**
     * Retrieve uploaded file tmp name by path
     *
     * @param string $path
     * @return string
     */
    public function getTmpName($path);

    /**
     * Retrieve uploaded file name by path
     *
     * @param string $path
     * @return string
     */
    public function getName($path);
}
