<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;

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
     * @param HttpRequest $request
     */
    public function __construct($workingDir, HttpRequest $request = null)
    {
        $request = $request ?: new HttpRequest();
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
