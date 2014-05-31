<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: IpAddress.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Validate_Ip
 */
#require_once 'Zend/Validate/Ip.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_IpLocation_IpAddress
{
    /**
     * the ip version
     * ip v4 = 4
     * ip v6 = 6
     *
     * @var integer
     */
    private $_version = 4;

    /**
     * currently supported versions
     *
     * @var array
     */
    private $_versionSupported = array(
        4,
        //6, not supported yet
    );

    private $_address = null;

    /**
     * create ipaddress object
     *
     * @param string $ip
     * @param integer $version
     *
     * @return Zend_Service_Developergarde_IpLocation_IpAddress
     */
    public function __construct($ip, $version = 4)
    {
        $this->setIp($ip)
             ->setVersion($version);
    }

    /**
     * sets new ip address
     *
     * @param string $ip
     * @throws Zend_Service_DeveloperGarden_Exception
     * @return Zend_Service_DeveloperGarden_IpLocation_IpAddress
     */
    public function setIp($ip)
    {
        $validator = new Zend_Validate_Ip();

        if (!$validator->isValid($ip)) {
            $message = $validator->getMessages();
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception($message['notIpAddress']);
        }
        $this->_address = $ip;
        return $this;
    }

    /**
     * returns the current address
     *
     * @return string
     */
    public function getIp()
    {
        return $this->_address;
    }

    /**
     * sets new ip version
     *
     * @param integer $version
     * @throws Zend_Service_DeveloperGarden_Exception
     * @return Zend_Service_DeveloperGarden_IpLocation_IpAddress
     */
    public function setVersion($version)
    {
        if (!in_array($version, $this->_versionSupported)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('Ip Version ' . (int)$version . ' is not supported.');
        }

        $this->_version = $version;
        return $this;
    }

    /**
     * returns the ip version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->_version;
    }
}
