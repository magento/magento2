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
 * @version    $Id: LocateIPResponse.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_BaseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/BaseType.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponse
    extends Zend_Service_DeveloperGarden_Response_BaseType
{
    /**
     * internal data object array of
     * elements
     *
     * @var array
     */
    public $ipAddressLocation = array();

    /**
     * constructor
     *
     * @param Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponseType $response
     */
    public function __construct(
        Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponseType $response
    ) {
        if ($response->ipAddressLocation instanceof Zend_Service_DeveloperGarden_Response_IpLocation_IPAddressLocationType) {
            if (is_array($response->ipAddressLocation)) {
                foreach ($response->ipAddressLocation as $location) {
                    $this->ipAddressLocation[] = $location;
                }

            } else {
                $this->ipAddressLocation[] = $response->ipAddressLocation;
            }
        } elseif (is_array($response->ipAddressLocation)) {
            $this->ipAddressLocation = $response->ipAddressLocation;
        }

        $this->errorCode     = $response->getErrorCode();
        $this->errorMessage  = $response->getErrorMessage();
        $this->statusCode    = $response->getStatusCode();
        $this->statusMessage = $response->getStatusMessage();
    }

    /**
     * implement own parsing mechanism to fix broken wsdl implementation
     */
    public function parse()
    {
        parent::parse();
        if (is_array($this->ipAddressLocation)) {
            foreach ($this->ipAddressLocation as $address) {
                $address->parse();
            }
        } elseif ($this->ipAddressLocation instanceof Zend_Service_DeveloperGarden_Response_IpLocation_IPAddressLocationType) {
            $this->ipAddressLocation->parse();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getIpAddressLocation()
    {
        return $this->ipAddressLocation;
    }
}
