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
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata_Books
 */
#require_once('Zend/Gdata/Books.php');

/**
 * Zend_Gdata_Query
 */
#require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for Books volumes
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Books_VolumeQuery extends Zend_Gdata_Query
{

    /**
     * Create Gdata_Books_VolumeQuery object
     *
     * @param string|null $url If non-null, pre-initializes the instance to
     *        use a given URL.
     */
    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    /**
     * Sets the minimum level of viewability of volumes to return in the search results
     *
     * @param string|null $value The minimum viewability - 'full' or 'partial'
     * @return Zend_Gdata_Books_VolumeQuery Provides a fluent interface
     */
    public function setMinViewability($value = null)
    {
        switch ($value) {
            case 'full_view':
                $this->_params['min-viewability'] = 'full';
                break;
            case 'partial_view':
                $this->_params['min-viewability'] = 'partial';
                break;
            case null:
                unset($this->_params['min-viewability']);
                break;
        }
        return $this;
    }

    /**
     * Minimum viewability of volumes to include in search results
     *
     * @return string|null min-viewability
     */
    public function getMinViewability()
    {
        if (array_key_exists('min-viewability', $this->_params)) {
            return $this->_params['min-viewability'];
        } else {
            return null;
        }
    }

    /**
     * Returns the generated full query URL
     *
     * @return string The URL
     */
    public function getQueryUrl()
    {
        if (isset($this->_url)) {
            $url = $this->_url;
        } else {
            $url = Zend_Gdata_Books::VOLUME_FEED_URI;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url = $url . $this->getQueryString();
        return $url;
    }

}
