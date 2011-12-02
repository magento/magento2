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
 * @version    $Id: SearchParameters.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
{
    /**
     * possible search parameters, incl. default values
     *
     * @var array
     */
    private $_parameters = array(
        'what'             => null,
        'dymwhat'          => null,
        'dymrelated'       => null,
        'hits'             => null,
        'collapse'         => null,
        'where'            => null,
        'dywhere'          => null,
        'radius'           => null,
        'lx'               => null,
        'ly'               => null,
        'rx'               => null,
        'ry'               => null,
        'transformgeocode' => null,
        'sort'             => null,
        'spatial'          => null,
        'sepcomm'          => null,
        'filter'           => null, // can be ONLINER or OFFLINER
        'openingtime'      => null, // can be now or HH::MM
        'kategorie'        => null, // @see http://www.suchen.de/kategorie-katalog
        'site'             => null,
        'typ'              => null,
        'name'             => null,
        'page'             => null,
        'city'             => null,
        'plz'              => null,
        'strasse'          => null,
        'bundesland'       => null,
    );

    /**
     * possible collapse values
     *
     * @var array
     */
    private $_possibleCollapseValues = array(
        true,
        false,
        'ADDRESS_COMPANY',
        'DOMAIN'
    );

    /**
     * sets a new search word
     * alias for setWhat
     *
     * @param string $searchValue
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setSearchValue($searchValue)
    {
        return $this->setWhat($searchValue);
    }

    /**
     * sets a new search word
     *
     * @param string $searchValue
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setWhat($searchValue)
    {
        $this->_parameters['what'] = $searchValue;
        return $this;
    }

    /**
     * enable the did you mean what feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function enableDidYouMeanWhat()
    {
        $this->_parameters['dymwhat'] = 'true';
        return $this;
    }

    /**
     * disable the did you mean what feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disableDidYouMeanWhat()
    {
        $this->_parameters['dymwhat'] = 'false';
        return $this;
    }

    /**
     * enable the did you mean where feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function enableDidYouMeanWhere()
    {
        $this->_parameters['dymwhere'] = 'true';
        return $this;
    }

    /**
     * disable the did you mean where feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disableDidYouMeanWhere()
    {
        $this->_parameters['dymwhere'] = 'false';
        return $this;
    }

    /**
     * enable did you mean related, if true Kihno will be corrected to Kino
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function enableDidYouMeanRelated()
    {
        $this->_parameters['dymrelated'] = 'true';
        return $this;
    }

    /**
     * diable did you mean related, if false Kihno will not be corrected to Kino
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disableDidYouMeanRelated()
    {
        $this->_parameters['dymrelated'] = 'true';
        return $this;
    }

    /**
     * set the max result hits for this search
     *
     * @param integer $hits
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setHits($hits = 10)
    {
        #require_once 'Zend/Validate/Between.php';
        $validator = new Zend_Validate_Between(0, 1000);
        if (!$validator->isValid($hits)) {
            $message = $validator->getMessages();
            #require_once 'Zend/Service/DeveloperGarden/LocalSearch/Exception.php';
            throw new Zend_Service_DeveloperGarden_LocalSearch_Exception(current($message));
        }
        $this->_parameters['hits'] = $hits;
        return $this;
    }

    /**
     * If true, addresses will be collapsed for a single domain, common values
     * are:
     * ADDRESS_COMPANY – to collapse by address
     * DOMAIN – to collapse by domain (same like collapse=true)
     * false
     *
     * @param mixed $value
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setCollapse($value)
    {
        if (!in_array($value, $this->_possibleCollapseValues, true)) {
            #require_once 'Zend/Service/DeveloperGarden/LocalSearch/Exception.php';
            throw new Zend_Service_DeveloperGarden_LocalSearch_Exception('Not a valid value provided.');
        }
        $this->_parameters['collapse'] = $value;
        return $this;
    }

    /**
     * set a specific search location
     * examples:
     * +47°54’53.10”, 11° 10’ 56.76”
     * 47°54’53.10;11°10’56.76”
     * 47.914750,11.182533
     * +47.914750 ; +11.1824
     * Darmstadt
     * Berlin
     *
     * @param string $where
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setWhere($where)
    {
        #require_once 'Zend/Validate/NotEmpty.php';

        $validator = new Zend_Validate_NotEmpty();
        if (!$validator->isValid($where)) {
            $message = $validator->getMessages();
            #require_once 'Zend/Service/DeveloperGarden/LocalSearch/Exception.php';
            throw new Zend_Service_DeveloperGarden_LocalSearch_Exception(current($message));
        }
        $this->_parameters['where'] = $where;
        return $this;
    }

    /**
     * returns the defined search location (ie city, country)
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->_parameters['where'];
    }

    /**
     * enable the spatial search feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function enableSpatial()
    {
        $this->_parameters['spatial'] = 'true';
        return $this;
    }

    /**
     * disable the spatial search feature
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disableSpatial()
    {
        $this->_parameters['spatial'] = 'false';
        return $this;
    }

    /**
     * sets spatial and the given radius for a circle search
     *
     * @param integer $radius
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setRadius($radius)
    {
        #require_once 'Zend/Validate/Int.php';

        $validator = new Zend_Validate_Int();
        if (!$validator->isValid($radius)) {
            $message = $validator->getMessages();
            #require_once 'Zend/Service/DeveloperGarden/LocalSearch/Exception.php';
            throw new Zend_Service_DeveloperGarden_LocalSearch_Exception(current($message));
        }
        $this->_parameters['radius'] = $radius;
        $this->_parameters['transformgeocode'] = 'false';

        return $this;
    }

    /**
     * sets the values for a rectangle search
     * lx = longitude left top
     * ly = latitude left top
     * rx = longitude right bottom
     * ry = latitude right bottom
     *
     * @param $lx
     * @param $ly
     * @param $rx
     * @param $ry
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setRectangle($lx, $ly, $rx, $ry)
    {
        $this->_parameters['lx'] = $lx;
        $this->_parameters['ly'] = $ly;
        $this->_parameters['rx'] = $rx;
        $this->_parameters['ry'] = $ry;

        return $this;
    }

    /**
     * if set, the service returns the zipcode for the result
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setTransformGeoCode()
    {
        $this->_parameters['transformgeocode'] = 'true';
        $this->_parameters['radius']           = null;

        return $this;
    }

    /**
     * sets the sort value
     * possible values are: 'relevance' and 'distance' (only with spatial enabled)
     *
     * @param string $sort
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setSort($sort)
    {
        if (!in_array($sort, array('relevance', 'distance'))) {
            #require_once 'Zend/Service/DeveloperGarden/LocalSearch/Exception.php';
            throw new Zend_Service_DeveloperGarden_LocalSearch_Exception('Not a valid sort value provided.');
        }

        $this->_parameters['sort'] = $sort;
        return $this;
    }

    /**
     * enable the separation of phone numbers
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function enablePhoneSeparation()
    {
        $this->_parameters['sepcomm'] = 'true';
        return $this;
    }

    /**
     * disable the separation of phone numbers
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disablePhoneSeparation()
    {
        $this->_parameters['sepcomm'] = 'true';
        return $this;
    }

    /**
     * if this filter is set, only results with a website are returned
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setFilterOnliner()
    {
        $this->_parameters['filter'] = 'ONLINER';
        return $this;
    }

    /**
     * if this filter is set, only results without a website are returned
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setFilterOffliner()
    {
        $this->_parameters['filter'] = 'OFFLINER';
        return $this;
    }


    /**
     * removes the filter value
     *
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function disableFilter()
    {
        $this->_parameters['filter'] = null;
        return $this;
    }

    /**
     * set a filter to get just results who are open at the given time
     * possible values:
     * now = open right now
     * HH:MM = at the given time (ie 20:00)
     *
     * @param string $time
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setOpeningTime($time = null)
    {
        $this->_parameters['openingtime'] = $time;
        return $this;
    }

    /**
     * sets a category filter
     *
     * @see http://www.suchen.de/kategorie-katalog
     * @param $category
     * @return unknown_type
     */
    public function setCategory($category = null)
    {
        $this->_parameters['kategorie'] = $category;
        return $this;
    }

    /**
     * sets the site filter
     * ie: www.developergarden.com
     *
     * @param string $site
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setSite($site)
    {
        $this->_parameters['site'] = $site;
        return $this;
    }

    /**
     * sets a filter to the given document type
     * ie: pdf, html
     *
     * @param string $type
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setDocumentType($type)
    {
        $this->_parameters['typ'] = $type;
        return $this;
    }

    /**
     * sets a filter for the company name
     * ie: Deutsche Telekom
     *
     * @param string $name
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setName($name)
    {
        $this->_parameters['name'] = $name;
        return $this;
    }

    /**
     * sets a filter for the zip code
     *
     * @param string $zip
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setZipCode($zip)
    {
        $this->_parameters['plz'] = $zip;
        return $this;
    }

    /**
     * sets a filter for the street
     *
     * @param string $street
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setStreet($street)
    {
        $this->_parameters['strasse'] = $street;
        return $this;
    }

    /**
     * sets a filter for the county
     *
     * @param string $county
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function setCounty($county)
    {
        $this->_parameters['bundesland'] = $county;
        return $this;
    }

    /**
     * sets a raw parameter with the value
     *
     * @param string $key
     * @param mixed $value
     * @return unknown_type
     */
    public function setRawParameter($key, $value)
    {
        $this->_parameters[$key] = $value;
        return $this;
    }

    /**
     * returns the parameters as an array
     *
     * @return array
     */
    public function getSearchParameters()
    {
        $retVal = array();
        foreach ($this->_parameters as $key => $value) {
            if ($value === null) {
                continue;
            }
            $param = array(
                'parameter' => $key,
                'value' => $value
            );
            $retVal[] = $param;
        }
        return $retVal;
    }
}
