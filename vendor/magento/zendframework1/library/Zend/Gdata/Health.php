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
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Exception
 */
#require_once 'Zend/Exception.php';

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * Service class for interacting with the Google Health Data API
 *
 * @link http://code.google.com/apis/health
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Health extends Zend_Gdata
{
    /**
     * URIs of the AuthSub/OAuth feeds.
     */
    const AUTHSUB_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/default';
    const AUTHSUB_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/default';

    /**
     * URIs of the ClientLogin feeds.
     */
    const CLIENTLOGIN_PROFILELIST_FEED_URI =
        'https://www.google.com/health/feeds/profile/list';
    const CLIENTLOGIN_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/ui';
    const CLIENTLOGIN_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/ui';

    /**
     * Authentication service names for Google Health and the H9 Sandbox.
     */
    const HEALTH_SERVICE_NAME = 'health';
    const H9_SANDBOX_SERVICE_NAME = 'weaver';

    /**
     * Create Zend_Gdata_Health object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *     when communicating with the Google Health servers.
     * @param string $applicationId The identity of the application in the form
     *     of Company-AppName-Version
     * @param bool $useH9Sandbox True if the H9 Developer's Sandbox should be
     *     used instead of production Google Health.
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0', $useH9Sandbox = false)
    {
        throw new Zend_Exception(
            'Google Health API has been discontinued by Google and was removed'
            . ' from Zend Framework in 1.12.0.  For more information see: '
            . 'http://googleblog.blogspot.ca/2011/06/update-on-google-health-and-google.html'
        );
    }
}
