<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Design editor request data
 */
namespace Magento\DesignEditor\Model\Config\Backend\File;

class RequestData implements \Magento\Backend\Model\Config\Backend\File\RequestData\RequestDataInterface
{
    /**
     * Retrieve uploaded file tmp name by path
     *
     * @param string $path
     * @return string
     */
    public function getTmpName($path)
    {
        return $this->_getParam('tmp_name', $path);
    }

    /**
     * Retrieve uploaded file name by path
     *
     * @param string $path
     * @return string
     */
    public function getName($path)
    {
        return $this->_getParam('name', $path);
    }

    /**
     * Get $_FILES superglobal value by path
     *
     * @param string $paramName
     * @return string
     */
    protected function _getParam($paramName)
    {
        $logoImage = reset($_FILES);
        if (empty($logoImage)) {
            return null;
        }
        return $logoImage[$paramName];
    }
}
