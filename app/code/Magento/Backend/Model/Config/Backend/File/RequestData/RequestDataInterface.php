<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
