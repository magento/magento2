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
 * @subpackage Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DocumentQuery.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Gdata_App_util
 */
#require_once('Zend/Gdata/App/Util.php');

/**
 * Zend_Gdata_Query
 */
#require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for Google Spreadsheets documents
 *
 * @link http://code.google.com/apis/gdata/spreadsheets/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_DocumentQuery extends Zend_Gdata_Query
{

    const SPREADSHEETS_FEED_URI = 'http://spreadsheets.google.com/feeds';

    protected $_defaultFeedUri = self::SPREADSHEETS_FEED_URI;
    protected $_documentType;
    protected $_visibility = 'private';
    protected $_projection = 'full';
    protected $_spreadsheetKey = null;
    protected $_worksheetId = null;

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_DocumentQuery object.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets the spreadsheet key for this query.
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setSpreadsheetKey($value)
    {
        $this->_spreadsheetKey = $value;
        return $this;
    }

    /**
     * Gets the spreadsheet key for this query.
     * @return string spreadsheet key
     */
    public function getSpreadsheetKey()
    {
        return $this->_spreadsheetKey;
    }

    /**
     * Sets the worksheet id for this query.
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setWorksheetId($value)
    {
        $this->_worksheetId = $value;
        return $this;
    }

    /**
     * Gets the worksheet id for this query.
     * @return string worksheet id
     */
    public function getWorksheetId()
    {
        return $this->_worksheetId;
    }

    /**
     * Sets the document type for this query.
     * @param string $value spreadsheets or worksheets
     * @return Zend_Gdata_Spreadsheets_DocumentQuery Provides a fluent interface
     */
    public function setDocumentType($value)
    {
        $this->_documentType = $value;
        return $this;
    }

    /**
     * Gets the document type for this query.
     * @return string document type
     */
    public function getDocumentType()
    {
        return $this->_documentType;
    }

    /**
     * Sets the projection for this query.
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_DocumentQuery Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    /**
     * Sets the visibility for this query.
     * @return Zend_Gdata_Spreadsheets_DocumentQuery Provides a fluent interface
     */
    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }

    /**
     * Gets the projection for this query.
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }

    /**
     * Gets the visibility for this query.
     * @return string visibility
     */
    public function getVisibility()
    {
        return $this->_visibility;
    }

    /**
     * Sets the title attribute for this query.
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_DocumentQuery Provides a fluent interface
     */
    public function setTitle($value)
    {
        if ($value != null) {
            $this->_params['title'] = $value;
        } else {
            unset($this->_params['title']);
        }
        return $this;
    }

    /**
     * Sets the title-exact attribute for this query.
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_DocumentQuery Provides a fluent interface
     */
    public function setTitleExact($value)
    {
        if ($value != null) {
            $this->_params['title-exact'] = $value;
        } else {
            unset($this->_params['title-exact']);
        }
        return $this;
    }

    /**
     * Gets the title attribute for this query.
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
     * Gets the title-exact attribute for this query.
     * @return string title-exact
     */
    public function getTitleExact()
    {
        if (array_key_exists('title-exact', $this->_params)) {
            return $this->_params['title-exact'];
        } else {
            return null;
        }
    }

    private function appendVisibilityProjection()
    {
        $uri = '';

        if ($this->_visibility != null) {
            $uri .= '/'.$this->_visibility;
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('A visibility must be provided for document queries.');
        }

        if ($this->_projection != null) {
            $uri .= '/'.$this->_projection;
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('A projection must be provided for document queries.');
        }

        return $uri;
    }


    /**
     * Gets the full query URL for this query.
     * @return string url
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;

        if ($this->_documentType != null) {
            $uri .= '/'.$this->_documentType;
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('A document type must be provided for document queries.');
        }

        if ($this->_documentType == 'spreadsheets') {
            $uri .= $this->appendVisibilityProjection();
            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            }
        } else if ($this->_documentType == 'worksheets') {
            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A spreadsheet key must be provided for worksheet document queries.');
            }
            $uri .= $this->appendVisibilityProjection();
            if ($this->_worksheetId != null) {
                $uri .= '/'.$this->_worksheetId;
            }
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

    /**
     * Gets the attribute query string for this query.
     * @return string query string
     */
    public function getQueryString()
    {
        return parent::getQueryString();
    }

}
