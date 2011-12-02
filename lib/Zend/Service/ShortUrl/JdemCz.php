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
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * @see Zend_Service_ShortUrl_AbstractShortener
 */
#require_once 'Zend/Service/ShortUrl/AbstractShortener.php';

/**
 * Jdem.cz API implementation
 *
 * @category   Zend
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_ShortUrl_JdemCz extends Zend_Service_ShortUrl_AbstractShortener
{
    /**
     * Base URI of the service
     *
     * @var string
     */
    protected $_baseUri = 'http://jdem.cz';
    
    /**
     * This function shortens long url
     *
     * @param string $url URL to Shorten
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid
     * @return string New URL
     */
    public function shorten($url)
    {
        $this->_validateUri($url);
        
        $serviceUri = 'http://www.jdem.cz/get';
        
        $this->getHttpClient()->setUri($serviceUri);
        $this->getHttpClient()->setParameterGet('url', $url);
        
        $response = $this->getHttpClient()->request();
        
        return $response->getBody();
    }

   /**
     * Reveals target for short URL
     *
     * @param string $shortenedUrl URL to reveal target of
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid or is not shortened by this service
     * @return string
     */
    public function unshorten($shortenedUrl)
    {
        $this->_validateUri($shortenedUrl);

        $this->_verifyBaseUri($shortenedUrl);
        
        $this->getHttpClient()->setUri($shortenedUrl)->setParameterGet('kam', 1);
        
        $response = $this->getHttpClient()->request();
        
        return $response->getBody();
    }
}
