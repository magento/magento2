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
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_Analytics_AccountEntry
 */
#require_once 'Zend/Gdata/Analytics/AccountEntry.php';

/**
 * @see Zend_Gdata_Analytics_AccountFeed
 */
#require_once 'Zend/Gdata/Analytics/AccountFeed.php';

/**
 * @see Zend_Gdata_Analytics_DataEntry
 */
#require_once 'Zend/Gdata/Analytics/DataEntry.php';

/**
 * @see Zend_Gdata_Analytics_DataFeed
 */
#require_once 'Zend/Gdata/Analytics/DataFeed.php';

/**
 * @see Zend_Gdata_Analytics_DataQuery
 */
#require_once 'Zend/Gdata/Analytics/DataQuery.php';

/**
 * @see Zend_Gdata_Analytics_AccountQuery
 */
#require_once 'Zend/Gdata/Analytics/AccountQuery.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics extends Zend_Gdata
{

    const AUTH_SERVICE_NAME = 'analytics';
    const ANALYTICS_FEED_URI = 'https://www.googleapis.com/analytics/v2.4/data';
    const ANALYTICS_ACCOUNT_FEED_URI = 'https://www.googleapis.com/analytics/v2.4/management/accounts';

    public static $namespaces = array(
        array('analytics', 'http://schemas.google.com/analytics/2009', 1, 0),
        array('ga', 'http://schemas.google.com/ga/2009', 1, 0)
     );

    /**
     * Create Gdata object
     *
     * @param Zend_Http_Client $client
     * @param string $applicationId The identity of the app in the form of
     *          Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Analytics');
        $this->registerPackage('Zend_Gdata_Analytics_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    /**
     * Retrieve account feed object
     *
     * @param string|Zend_Uri_Uri $uri
     * @return Zend_Gdata_Analytics_AccountFeed
     */
    public function getAccountFeed($uri = self::ANALYTICS_ACCOUNT_FEED_URI)
    {
        if ($uri instanceof Query) {
            $uri = $uri->getQueryUrl();
        }
        return parent::getFeed($uri, 'Zend_Gdata_Analytics_AccountFeed');
    }

    /**
     * Retrieve data feed object
     *
     * @param string|Zend_Uri_Uri $uri
     * @return Zend_Gdata_Analytics_DataFeed
     */
    public function getDataFeed($uri = self::ANALYTICS_FEED_URI)
    {
        if ($uri instanceof Query) {
            $uri = $uri->getQueryUrl();
        }
        return parent::getFeed($uri, 'Zend_Gdata_Analytics_DataFeed');
    }

    /**
     * Returns a new DataQuery object.
     *
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function newDataQuery()
    {
        return new Zend_Gdata_Analytics_DataQuery();
    }

    /**
     * Returns a new AccountQuery object.
     *
     * @return Zend_Gdata_Analytics_AccountQuery
     */
    public function newAccountQuery()
    {
        return new Zend_Gdata_Analytics_AccountQuery();
    }
}
