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
 * @version    $Id: IPAddressLocationType.php 20166 2010-01-09 19:00:17Z bkarwin $
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
class Zend_Service_DeveloperGarden_Response_IpLocation_IPAddressLocationType
    extends Zend_Service_DeveloperGarden_Response_BaseType
{
    /**
     * @var Zend_Service_DeveloperGarden_Response_IpLocation_RegionType
     */
    public $isInRegion = null;

    /**
     * @var Zend_Service_DeveloperGarden_Response_IpLocation_GeoCoordinatesType
     */
    public $isInGeo = null;

    /**
     * @var Zend_Service_DeveloperGarden_Response_IpLocation_CityType
     */
    public $isInCity = null;

    /**
     * @var integer
     */
    public $ipType = null;

    /**
     * @var string
     */
    public $ipAddress = null;

    /**
     * @var integer
     */
    public $radius = 0;

    /**
     * @return Zend_Service_DeveloperGarden_Response_IpLocation_RegionType
     */
    public function getRegion()
    {
        return $this->isInRegion;
    }

    /**
     * @return Zend_Service_DeveloperGarden_Response_IpLocation_GeoCoordinatesType
     */
    public function getGeoCoordinates()
    {
        return $this->isInGeo;
    }

    /**
     * @return Zend_Service_DeveloperGarden_Response_IpLocation_CityType
     */
    public function getCity()
    {
        return $this->isInCity;
    }

    /**
     * @return integer
     */
    public function getIpType()
    {
        return $this->ipType;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return integer
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * implement parsing
     *
     */
    public function parse()
    {
        parent::parse();
        if ($this->isInCity === null) {
            #require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/CityType.php';
            $this->isInCity = new Zend_Service_DeveloperGarden_Response_IpLocation_CityType();
        }

        if ($this->isInRegion === null) {
            #require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/RegionType.php';
            $this->isInRegion = new Zend_Service_DeveloperGarden_Response_IpLocation_RegionType();
        }

        if ($this->isInGeo === null) {
            #require_once 'Zend/Service/DeveloperGarden/Response/IpLocation/GeoCoordinatesType.php';
            $this->isInGeo = new Zend_Service_DeveloperGarden_Response_IpLocation_GeoCoordinatesType();
        }

        return $this;
    }
}
