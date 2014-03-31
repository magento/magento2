<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 * 
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
