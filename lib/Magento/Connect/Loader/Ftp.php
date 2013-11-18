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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class for ftp loader which using in the Rest
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Loader;

class Ftp
{

    const TEMPORARY_DIR = 'var/package/tmp';

    const FTP_USER = 'magconnect';

    const FTP_PASS = '4SyTUxPts0o2';

    /**
    * Object of Ftp
    *
    * @var \Magento\Connect\Ftp
    */
    protected $_ftp = null;

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
        $this->_ftp = new \Magento\Connect\Ftp();
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
        $uri = str_replace('ftp://', '', $uri);
        $uri = self::FTP_USER.":".self::FTP_PASS."@".$uri;
        $this->getFtp()->connect("ftp://".$uri);
        $this->getFtp()->pasv(true);
        $localFile = self::TEMPORARY_DIR.DS.time().".xml";

        if ($this->getFtp()->get($localFile, $remoteFile)) {
            $this->_responseBody = file_get_contents($localFile);
            $this->_responseStatus = 200;
        }
        @unlink($localFile);
        $this->getFtp()->close();
        return $out;
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

}
