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
 * @package    Zend_Navigation
 * @subpackage Page
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Navigation_Page_Abstract
 */
#require_once 'Zend/Navigation/Page.php';

/**
 * Represents a page that is defined by specifying a URI
 *
 * @category   Zend
 * @package    Zend_Navigation
 * @subpackage Page
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Navigation_Page_Uri extends Zend_Navigation_Page
{
    /**
     * Page URI
     *
     * @var string|null
     */
    protected $_uri = null;

    /**
     * Sets page URI
     *
     * @param  string $uri                page URI, must a string or null
     * @return Zend_Navigation_Page_Uri   fluent interface, returns self
     * @throws Zend_Navigation_Exception  if $uri is invalid
     */
    public function setUri($uri)
    {
        if (null !== $uri && !is_string($uri)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $uri must be a string or null');
        }

        $this->_uri = $uri;
        return $this;
    }

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Returns href for this page
     *
     * @return string
     */
    public function getHref()
    {
        $uri = $this->getUri();

        $fragment = $this->getFragment();
        if (null !== $fragment) {
            if ('#' == substr($uri, -1)) {
                return $uri . $fragment;
            } else {
                return $uri . '#' . $fragment;
            }
        }

        return $uri;
    }

    // Public methods:

    /**
     * Returns an array representation of the page
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'uri' => $this->getUri()
            ));
    }
}
