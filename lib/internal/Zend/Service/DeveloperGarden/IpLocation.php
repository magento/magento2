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
 * @version    $Id: IpLocation.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Client/ClientAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/LocateIPResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/LocateIPResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_IPAddressLocationType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/IPAddressLocationType.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_RegionType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/RegionType.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_GeoCoordinatesType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/GeoCoordinatesType.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_IpLocation_CityType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/CityType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_IpLocation_LocateIPRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/IpLocation/LocateIPRequest.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_IpLocation 
    extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-iplocation/services/IPLocation?wsdl';

    /**
     * wsdl file local
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/IPLocation.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        'LocateIPResponseType'  => 'Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponseType',
        'IPAddressLocationType' => 'Zend_Service_DeveloperGarden_Response_IpLocation_IPAddressLocationType',
        'RegionType'            => 'Zend_Service_DeveloperGarden_Response_IpLocation_RegionType',
        'GeoCoordinatesType'    => 'Zend_Service_DeveloperGarden_Response_IpLocation_GeoCoordinatesType',
        'CityType'              => 'Zend_Service_DeveloperGarden_Response_IpLocation_CityType',
    );

    /**
     * locate the given Ip address or array of addresses
     *
     * @param Zend_Service_DeveloperGarden_IpLocation_IpAddress|string $ip
     * @return Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponse
     */
    public function locateIP($ip)
    {
        $request = new Zend_Service_DeveloperGarden_Request_IpLocation_LocateIPRequest(
            $this->getEnvironment(),
            $ip
        );

        $result = $this->getSoapClient()->locateIP($request);

        $response = new Zend_Service_DeveloperGarden_Response_IpLocation_LocateIPResponse($result);
        return $response->parse();
    }
}
