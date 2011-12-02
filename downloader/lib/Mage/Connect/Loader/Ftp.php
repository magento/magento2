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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class for ftp loader which using in the Rest
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Loader_Ftp
{

    const TEMPORARY_DIR = '../var/package/tmp';

    const FTP_USER = 'anonymous';

    const FTP_PASS = 'test@gmail.com';

    /**
    * Object of Ftp
    *
    * @var Mage_Connect_Ftp
    */
    protected $_ftp = null;

    /**
     * User name
     * @var string
     */
    protected $_ftpUser = '';

    /**
     * User password
     * @var string
     */
    protected $_ftpPassword = '';

    /**
     * Response body
     * @var string
     */
    protected $_responseBody = '';

    /**
     * Response status
     * @var int
     */
    protected $_responseStatus = 0;

    /**
    * Constructor
    */
    public function __construct()
    {
        $this->_ftp = new Mage_Connect_Ftp();
        $this->_ftpUser = self::FTP_USER;
        $this->_ftpPassword = self::FTP_PASS;
    }

    public function getFtp()
    {
        return $this->_ftp;
    }

    /**
    * Retrieve file from URI
    *
    * @param mixed $uri
    * @return bool
    */
    public function get($uri)
    {
        $remoteFile = basename($uri);
        $uri = dirname($uri);
        $uri = str_replace('http://', '', $uri);
        $uri = str_replace('https://', '', $uri);
        $uri = str_replace('ftp://', '', $uri);
        $uri = $this->_ftpUser.":".$this->_ftpPassword."@".$uri;
        $this->getFtp()->connect("ftp://".$uri);
        $this->getFtp()->pasv(true);
        $tmpDir = self::TEMPORARY_DIR . DS;
        if (!is_dir($tmpDir)) {
            $tmpDir = sys_get_temp_dir();
        }
        if (substr($tmpDir, -1) != DS) {
            $tmpDir .= DS;
        }
        $localFile = $tmpDir . time() . ".xml";

        if ($this->getFtp()->get($localFile, $remoteFile)) {
            $this->_responseBody = file_get_contents($localFile);
            $this->_responseStatus = 200;
        }
        @unlink($localFile);
        $this->getFtp()->close();
        return $this;
    }

    /**
     * Get response status code
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_responseStatus;
    }

    /**
    * put your comment there...
    *
    * @return string
    */
    public function getBody()
    {
        return $this->_responseBody;
    }

    /**
    * Set login credentials for ftp auth.
    * @param string $ftpLogin Ftp User account name
    * @param string $ftpPassword User password
    * @return string
    */
    public function setCredentials($ftpLogin, $ftpPassword)
    {
        $this->_ftpUser = $ftpLogin;
        $this->_ftpPassword = $ftpPassword;
    }

}
