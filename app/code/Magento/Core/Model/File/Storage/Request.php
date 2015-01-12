<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\File\Storage;

class Request
{
    /**
     * Path info
     *
     * @var string
     */
    protected $_pathInfo;

    /**
     * Requested file path
     *
     * @var string
     */
    protected $_filePath;

    /**
     * @param string $workingDir
     * @param \Zend_Controller_Request_Http $request
     */
    public function __construct($workingDir, \Zend_Controller_Request_Http $request = null)
    {
        $request = $request ?: new \Zend_Controller_Request_Http();
        $this->_pathInfo = str_replace('..', '', ltrim($request->getPathInfo(), '/'));
        $this->_filePath = $workingDir . '/' . $this->_pathInfo;
    }

    /**
     * Retrieve path info
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->_pathInfo;
    }

    /**
     * Retrieve file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filePath;
    }
}
