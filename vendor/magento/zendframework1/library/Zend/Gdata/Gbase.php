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
 * @subpackage Gbase
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
 * Service class for interacting with the Google Base data API
 *
 * @link http://code.google.com/apis/base
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase extends Zend_Gdata
{

    /**
     * Path to the customer items feeds on the Google Base server.
     */
    const GBASE_ITEM_FEED_URI = 'https://www.google.com/base/feeds/items';

    /**
     * Path to the snippets feeds on the Google Base server.
     */
    const GBASE_SNIPPET_FEED_URI = 'https://www.google.com/base/feeds/snippets';

    /**
     * Authentication service name for Google Base
     */
    const AUTH_SERVICE_NAME = 'gbase';

    /**
     * Create Zend_Gdata_Gbase object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google Apps servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        throw new Zend_Exception(
            'Google Base API has been discontinued by Google and was removed'
            . ' from Zend Framework in 1.12.0.  For more information see: '
            . 'http://googlemerchantblog.blogspot.ca/2010/12/new-shopping-apis-and-deprecation-of.html'
        );
    }
}
