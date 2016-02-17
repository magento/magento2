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
 * @package    Zend_Gdata
 * @subpackage Analytics
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Query
 */
#require_once 'Zend/Gdata/Query.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_DataQuery extends Zend_Gdata_Query
{
    const ANALYTICS_FEED_URI = 'https://www.googleapis.com/analytics/v2.4/data';

    /**
     * The default URI used for feeds.
     */
    protected $_defaultFeedUri = self::ANALYTICS_FEED_URI;

    // D1. Visitor
    const DIMENSION_BROWSER = 'ga:browser';
    const DIMENSION_BROWSER_VERSION = 'ga:browserVersion';
    const DIMENSION_CITY = 'ga:city';
    const DIMENSION_CONNECTIONSPEED = 'ga:connectionSpeed';
    const DIMENSION_CONTINENT = 'ga:continent';
    const DIMENSION_COUNTRY = 'ga:country';
    const DIMENSION_DATE = 'ga:date';
    const DIMENSION_DAY = 'ga:day';
    const DIMENSION_DAYS_SINCE_LAST_VISIT= 'ga:daysSinceLastVisit';
    const DIMENSION_FLASH_VERSION = 'ga:flashVersion';
    const DIMENSION_HOSTNAME = 'ga:hostname';
    const DIMENSION_HOUR = 'ga:hour';
    const DIMENSION_JAVA_ENABLED= 'ga:javaEnabled';
    const DIMENSION_LANGUAGE= 'ga:language';
    const DIMENSION_LATITUDE = 'ga:latitude';
    const DIMENSION_LONGITUDE = 'ga:longitude';
    const DIMENSION_MONTH = 'ga:month';
    const DIMENSION_NETWORK_DOMAIN = 'ga:networkDomain';
    const DIMENSION_NETWORK_LOCATION = 'ga:networkLocation';
    const DIMENSION_OPERATING_SYSTEM = 'ga:operatingSystem';
    const DIMENSION_OPERATING_SYSTEM_VERSION = 'ga:operatingSystemVersion';
    const DIMENSION_PAGE_DEPTH = 'ga:pageDepth';
    const DIMENSION_REGION = 'ga:region';
    const DIMENSION_SCREEN_COLORS= 'ga:screenColors';
    const DIMENSION_SCREEN_RESOLUTION = 'ga:screenResolution';
    const DIMENSION_SUB_CONTINENT = 'ga:subContinent';
    const DIMENSION_USER_DEFINED_VALUE = 'ga:userDefinedValue';
    const DIMENSION_VISIT_COUNT = 'ga:visitCount';
    const DIMENSION_VISIT_LENGTH = 'ga:visitLength';
    const DIMENSION_VISITOR_TYPE = 'ga:visitorType';
    const DIMENSION_WEEK = 'ga:week';
    const DIMENSION_YEAR = 'ga:year';

    // D2. Campaign
    const DIMENSION_AD_CONTENT = 'ga:adContent';
    const DIMENSION_AD_GROUP = 'ga:adGroup';
    const DIMENSION_AD_SLOT = 'ga:adSlot';
    const DIMENSION_AD_SLOT_POSITION = 'ga:adSlotPosition';
    const DIMENSION_CAMPAIGN = 'ga:campaign';
    const DIMENSION_KEYWORD = 'ga:keyword';
    const DIMENSION_MEDIUM = 'ga:medium';
    const DIMENSION_REFERRAL_PATH = 'ga:referralPath';
    const DIMENSION_SOURCE = 'ga:source';

    // D3. Content
    const DIMENSION_EXIT_PAGE_PATH = 'ga:exitPagePath';
    const DIMENSION_LANDING_PAGE_PATH = 'ga:landingPagePath';
    const DIMENSION_PAGE_PATH = 'ga:pagePath';
    const DIMENSION_PAGE_TITLE = 'ga:pageTitle';
    const DIMENSION_SECOND_PAGE_PATH = 'ga:secondPagePath';

    // D4. Ecommerce
    const DIMENSION_AFFILIATION = 'ga:affiliation';
    const DIMENSION_DAYS_TO_TRANSACTION = 'ga:daysToTransaction';
    const DIMENSION_PRODUCT_CATEGORY = 'ga:productCategory';
    const DIMENSION_PRODUCT_NAME = 'ga:productName';
    const DIMENSION_PRODUCT_SKU = 'ga:productSku';
    const DIMENSION_TRANSACTION_ID = 'ga:transactionId';
    const DIMENSION_VISITS_TO_TRANSACTION = 'ga:visitsToTransaction';

    // D5. Internal Search
    const DIMENSION_SEARCH_CATEGORY = 'ga:searchCategory';
    const DIMENSION_SEARCH_DESTINATION_PAGE = 'ga:searchDestinationPage';
    const DIMENSION_SEARCH_KEYWORD = 'ga:searchKeyword';
    const DIMENSION_SEARCH_KEYWORD_REFINEMENT = 'ga:searchKeywordRefinement';
    const DIMENSION_SEARCH_START_PAGE = 'ga:searchStartPage';
    const DIMENSION_SEARCH_USED = 'ga:searchUsed';

    // D6. Navigation
    const DIMENSION_NEXT_PAGE_PATH = 'ga:nextPagePath';
    const DIMENSION_PREV_PAGE_PATH= 'ga:previousPagePath';

    // D7. Events
    const DIMENSION_EVENT_CATEGORY = 'ga:eventCategory';
    const DIMENSION_EVENT_ACTION = 'ga:eventAction';
    const DIMENSION_EVENT_LABEL = 'ga:eventLabel';

    // D8. Custon Variables
    const DIMENSION_CUSTOM_VAR_NAME_1 = 'ga:customVarName1';
    const DIMENSION_CUSTOM_VAR_NAME_2 = 'ga:customVarName2';
    const DIMENSION_CUSTOM_VAR_NAME_3 = 'ga:customVarName3';
    const DIMENSION_CUSTOM_VAR_NAME_4 = 'ga:customVarName4';
    const DIMENSION_CUSTOM_VAR_NAME_5 = 'ga:customVarName5';
    const DIMENSION_CUSTOM_VAR_VALUE_1 = 'ga:customVarValue1';
    const DIMENSION_CUSTOM_VAR_VALUE_2 = 'ga:customVarValue2';
    const DIMENSION_CUSTOM_VAR_VALUE_3 = 'ga:customVarValue3';
    const DIMENSION_CUSTOM_VAR_VALUE_4 = 'ga:customVarValue4';
    const DIMENSION_CUSTOM_VAR_VALUE_5 = 'ga:customVarValue5';

    // M1. Visitor
    const METRIC_BOUNCES = 'ga:bounces';
    const METRIC_ENTRANCES = 'ga:entrances';
    const METRIC_EXITS = 'ga:exits';
    const METRIC_NEW_VISITS = 'ga:newVisits';
    const METRIC_PAGEVIEWS = 'ga:pageviews';
    const METRIC_TIME_ON_PAGE = 'ga:timeOnPage';
    const METRIC_TIME_ON_SITE = 'ga:timeOnSite';
    const METRIC_VISITORS = 'ga:visitors';
    const METRIC_VISITS = 'ga:visits';

    // M2. Campaign
    const METRIC_AD_CLICKS = 'ga:adClicks';
    const METRIC_AD_COST = 'ga:adCost';
    const METRIC_CPC = 'ga:CPC';
    const METRIC_CPM = 'ga:CPM';
    const METRIC_CTR = 'ga:CTR';
    const METRIC_IMPRESSIONS = 'ga:impressions';

    // M3. Content
    const METRIC_UNIQUE_PAGEVIEWS = 'ga:uniquePageviews';

    // M4. Ecommerce
    const METRIC_ITEM_REVENUE = 'ga:itemRevenue';
    const METRIC_ITEM_QUANTITY = 'ga:itemQuantity';
    const METRIC_TRANSACTIONS = 'ga:transactions';
    const METRIC_TRANSACTION_REVENUE = 'ga:transactionRevenue';
    const METRIC_TRANSACTION_SHIPPING = 'ga:transactionShipping';
    const METRIC_TRANSACTION_TAX = 'ga:transactionTax';
    const METRIC_UNIQUE_PURCHASES = 'ga:uniquePurchases';

    // M5. Internal Search
    const METRIC_SEARCH_DEPTH = 'ga:searchDepth';
    const METRIC_SEARCH_DURATION = 'ga:searchDuration';
    const METRIC_SEARCH_EXITS = 'ga:searchExits';
    const METRIC_SEARCH_REFINEMENTS = 'ga:searchRefinements';
    const METRIC_SEARCH_UNIQUES = 'ga:searchUniques';
    const METRIC_SEARCH_VISIT = 'ga:searchVisits';

    // M6. Goals
    const METRIC_GOAL_COMPLETIONS_ALL = 'ga:goalCompletionsAll';
    const METRIC_GOAL_STARTS_ALL = 'ga:goalStartsAll';
    const METRIC_GOAL_VALUE_ALL = 'ga:goalValueAll';
    // TODO goals 1-20
    const METRIC_GOAL_1_COMPLETION = 'ga:goal1Completions';
    const METRIC_GOAL_1_STARTS = 'ga:goal1Starts';
    const METRIC_GOAL_1_VALUE = 'ga:goal1Value';

    // M7. Events
    const METRIC_TOTAL_EVENTS = 'ga:totalEvents';
    const METRIC_UNIQUE_EVENTS = 'ga:uniqueEvents';
    const METRIC_EVENT_VALUE = 'ga:eventValue';

    // suported filter operators
    const EQUALS = "==";
    const EQUALS_NOT = "!=";
    const GREATER = ">";
    const LESS = ">";
    const GREATER_EQUAL = ">=";
    const LESS_EQUAL = "<=";
    const CONTAINS = "=@";
    const CONTAINS_NOT ="!@";
    const REGULAR ="=~";
    const REGULAR_NOT ="!~";

    /**
     * @var string
     */
    protected $_profileId;
    /**
     * @var array
     */
    protected $_dimensions = array();
    /**
     * @var array
     */
    protected $_metrics = array();
    /**
     * @var array
     */
    protected $_sort = array();
    /**
     * @var array
     */
    protected $_filters = array();

    /**
     * @param string $id
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setProfileId($id)
    {
        $this->_profileId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * @param string $dimension
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addDimension($dimension)
    {
        $this->_dimensions[$dimension] = true;
        return $this;
    }

    /**
     * @param string $metric
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addMetric($metric)
    {
        $this->_metrics[$metric] = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->_dimensions;
    }

    /**
     * @return array
     */
    public function getMetrics()
    {
        return $this->_metrics;
    }

    /**
     * @param string $dimension
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function removeDimension($dimension)
    {
        unset($this->_dimensions[$dimension]);
        return $this;
    }
    /**
     * @param string $metric
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function removeMetric($metric)
    {
        unset($this->_metrics[$metric]);
        return $this;
    }
    /**
     * @param string $value
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setStartDate($date)
    {
        $this->setParam("start-date", $date);
        return $this;
    }
    /**
     * @param string $value
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setEndDate($date)
    {
        $this->setParam("end-date", $date);
        return $this;
    }

    /**
     * @param string $filter
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addFilter($filter)
    {
        $this->_filters[] = array($filter, true);
        return $this;
    }

    /**
     * @param string $filter
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addOrFilter($filter)
    {
        $this->_filters[] = array($filter, false);
        return $this;
    }

    /**
     * @param string $sort
     * @param boolean[optional] $descending
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addSort($sort, $descending=false)
    {
        // add to sort storage
        $this->_sort[] = ($descending?'-':'').$sort;
        return $this;
    }

    /**
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function clearSort()
    {
        $this->_sort = array();
        return $this;
    }

    /**
     * @param string $segment
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setSegment($segment)
    {
        $this->setParam('segment', $segment);
        return $this;
    }

    /**
     * @return string url
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        if (isset($this->_url)) {
            $uri = $this->_url;
        }

        $dimensions = $this->getDimensions();
        if (!empty($dimensions)) {
            $this->setParam('dimensions', implode(",", array_keys($dimensions)));
        }

        $metrics = $this->getMetrics();
        if (!empty($metrics)) {
            $this->setParam('metrics', implode(",", array_keys($metrics)));
        }

        // profile id (ga:tableId)
        if ($this->getProfileId() != null) {
            $this->setParam('ids', 'ga:'.ltrim($this->getProfileId(), "ga:"));
        }

        // sorting
        if ($this->_sort) {
            $this->setParam('sort', implode(",", $this->_sort));
        }

        // filtering
        $filters = "";
        foreach ($this->_filters as $filter) {
            $filters.=($filter[1]===true?';':',').$filter[0];
        }

        if ($filters!="") {
            $this->setParam('filters', ltrim($filters, ",;"));
        }

        $uri .= $this->getQueryString();
        return $uri;
    }
}
