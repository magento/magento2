<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Request data interface
 */
namespace Magento\Config\Model\Config\Backend\File\RequestData;

/**
 * @api
 * @since 2.0.0
 */
interface RequestDataInterface
{
    /**
     * Retrieve uploaded file tmp name by path
     *
     * @param string $path
     * @return string
     * @since 2.0.0
     */
    public function getTmpName($path);

    /**
     * Retrieve uploaded file name by path
     *
     * @param string $path
     * @return string
     * @since 2.0.0
     */
    public function getName($path);
}
