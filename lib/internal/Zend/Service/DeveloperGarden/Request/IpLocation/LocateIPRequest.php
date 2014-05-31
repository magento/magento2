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
 * @version    $Id: LocateIPRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_RequestAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Request/RequestAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_IpLocation_IpAddress
 */
#require_once 'Zend/Service/DeveloperGarden/IpLocation/IpAddress.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_IpLocation_LocateIPRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * the ip addresses to lookup for
     *
     * @var Zend_Service_DeveloperGarden_Request_IpLocation_IpAddress
     */
    public $address = null;

    /**
     * the account
     *
     * @var string
     */
    public $account = null;

    /**
     * constructor give them the environment
     *
     * @param integer $environment
     * @param Zend_Service_DeveloperGarden_IpLocation_IpAddress|array $ip
     *
     * @return Zend_Service_DeveloperGarden_Request_RequestAbstract
     */
    public function __construct($environment, $ip = null)
    {
        parent::__construct($environment);

        if ($ip !== null) {
            $this->setIp($ip);
        }
    }

    /**
     * sets new ip or array of ips
     *
     * @param Zend_Service_DeveloperGarden_IpLocation_IpAddress|array $ip
     *
     * @return Zend_Service_DeveloperGarden_Request_IpLocation_LocateIPRequest
     */
    public function setIp($ip)
    {
        if ($ip instanceof Zend_Service_DeveloperGarden_IpLocation_IpAddress) {
            $this->address[] = array(
                'ipType'    => $ip->getVersion(),
                'ipAddress' => $ip->getIp(),
            );
            return $this;
        }

        if (is_array($ip)) {
            foreach ($ip as $ipObject) {
                if (!$ipObject instanceof Zend_Service_DeveloperGarden_IpLocation_IpAddress
                    && !is_string($ipObject)
                ) {
                    #require_once 'Zend/Service/DeveloperGarden/Request/Exception.php';
                    throw new Zend_Service_DeveloperGarden_Request_Exception(
                        'Not a valid Ip Address object found.'
                    );
                }
                $this->setIp($ipObject);
            }
            return $this;
        }

        if (!is_string($ip)) {
            #require_once 'Zend/Service/DeveloperGarden/Request/Exception.php';
            throw new Zend_Service_DeveloperGarden_Request_Exception('Not a valid Ip Address object found.');
        }

        return $this->setIp(new Zend_Service_DeveloperGarden_IpLocation_IpAddress($ip));
    }
}
