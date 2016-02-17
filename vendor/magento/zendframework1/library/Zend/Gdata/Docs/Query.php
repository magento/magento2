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
 * @subpackage Docs
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata_Query
 */
#require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for Google Document List documents
 *
 * @link http://code.google.com/apis/gdata/spreadsheets/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Docs
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Docs_Query extends Zend_Gdata_Query
{

    /**
     * The base URL for retrieving a document list
     *
     * @var string
     */
    const DOCUMENTS_LIST_FEED_URI = 'https://docs.google.com/feeds/documents';

    /**
     * The generic base URL used by some inherited methods
     *
     * @var string
     */
    protected $_defaultFeedUri = self::DOCUMENTS_LIST_FEED_URI;

    /**
     * The visibility to be used when querying for the feed. A request for a
     * feed with private visbility requires the user to be authenricated.
     * Private is the only avilable visibility for the documents list.
     *
     * @var string
     */
    protected $_visibility = 'private';

    /**
     * The projection determines how much detail should be given in the
     * result of the query. Full is the only valid projection for the
     * documents list.
     *
     * @var string
     */
    protected $_projection = 'full';

    /**
     * Constructs a new instance of a Zend_Gdata_Docs_Query object.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets the projection for this query. Common values for projection
     * include 'full'.
     *
     * @param string $value
     * @return Zend_Gdata_Docs_Query Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    /**
     * Sets the visibility for this query. Common values for visibility
     * include 'private'.
     *
     * @return Zend_Gdata_Docs_Query Provides a fluent interface
     */
    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }

    /**
     * Gets the projection for this query.
     *
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }

    /**
     * Gets the visibility for this query.
     *
     * @return string visibility
     */
    public function getVisibility()
    {
        return $this->_visibility;
    }

    /**
     * Sets the title attribute for this query. The title parameter is used
     * to restrict the results to documents whose titles either contain or
     * completely match the title.
     *
     * @param string $value
     * @return Zend_Gdata_Docs_Query Provides a fluent interface
     */
    public function setTitle($value)
    {
        if ($value !== null) {
            $this->_params['title'] = $value;
        } else {
            unset($this->_params['title']);
        }
        return $this;
    }

    /**
     * Gets the title attribute for this query.
     *
     * @return string title
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->_params)) {
            return $this->_params['title'];
        } else {
            return null;
        }
    }

    /**
     * Sets the title-exact attribute for this query.
     * If title-exact is set to true, the title query parameter will be used
     * in an exact match. Only documents with a title identical to the
     * title parameter will be returned.
     *
     * @param boolean $value Use either true or false
     * @return Zend_Gdata_Docs_Query Provides a fluent interface
     */
    public function setTitleExact($value)
    {
        if ($value) {
            $this->_params['title-exact'] = $value;
        } else {
            unset($this->_params['title-exact']);
        }
        return $this;
    }

    /**
     * Gets the title-exact attribute for this query.
     *
     * @return string title-exact
     */
    public function getTitleExact()
    {
        if (array_key_exists('title-exact', $this->_params)) {
            return $this->_params['title-exact'];
        } else {
            return false;
        }
    }

    /**
     * Gets the full query URL for this query.
     *
     * @return string url
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;

        if ($this->_visibility !== null) {
            $uri .= '/' . $this->_visibility;
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'A visibility must be provided for cell queries.');
        }

        if ($this->_projection !== null) {
            $uri .= '/' . $this->_projection;
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'A projection must be provided for cell queries.');
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

}
