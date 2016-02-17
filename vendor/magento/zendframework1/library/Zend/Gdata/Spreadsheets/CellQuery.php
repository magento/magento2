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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
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
 * Assists in constructing queries for Google Spreadsheets cells
 *
 * @link http://code.google.com/apis/gdata/spreadsheets/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_CellQuery extends Zend_Gdata_Query
{

    const SPREADSHEETS_CELL_FEED_URI = 'https://spreadsheets.google.com/feeds/cells';

    protected $_defaultFeedUri = self::SPREADSHEETS_CELL_FEED_URI;
    protected $_visibility = 'private';
    protected $_projection = 'full';
    protected $_spreadsheetKey = null;
    protected $_worksheetId = 'default';
    protected $_cellId = null;

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_CellQuery object.
     *
     * @param string $url Base URL to use for queries
     */
    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    /**
     * Sets the spreadsheet key for this query.
     *
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
     *
     * @return string spreadsheet key
     */
    public function getSpreadsheetKey()
    {
        return $this->_spreadsheetKey;
    }

    /**
     * Sets the worksheet id for this query.
     *
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
     *
     * @return string worksheet id
     */
    public function getWorksheetId()
    {
        return $this->_worksheetId;
    }

    /**
     * Sets the cell id for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setCellId($value)
    {
        $this->_cellId = $value;
        return $this;
    }

    /**
     * Gets the cell id for this query.
     *
     * @return string cell id
     */
    public function getCellId()
    {
        return $this->_cellId;
    }

    /**
     * Sets the projection for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    /**
     * Sets the visibility for this query.
     *
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
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
     * Sets the min-row attribute for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setMinRow($value)
    {
        if ($value != null) {
            $this->_params['min-row'] = $value;
        } else {
            unset($this->_params['min-row']);
        }
        return $this;
    }

    /**
     * Gets the min-row attribute for this query.
     *
     * @return string min-row
     */
    public function getMinRow()
    {
        if (array_key_exists('min-row', $this->_params)) {
            return $this->_params['min-row'];
        } else {
            return null;
        }
    }

    /**
     * Sets the max-row attribute for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setMaxRow($value)
    {
        if ($value != null) {
            $this->_params['max-row'] = $value;
        } else {
            unset($this->_params['max-row']);
        }
        return $this;
    }

    /**
     * Gets the max-row attribute for this query.
     *
     * @return string max-row
     */
    public function getMaxRow()
    {
        if (array_key_exists('max-row', $this->_params)) {
            return $this->_params['max-row'];
        } else {
            return null;
        }
    }

    /**
     * Sets the min-col attribute for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setMinCol($value)
    {
        if ($value != null) {
            $this->_params['min-col'] = $value;
        } else {
            unset($this->_params['min-col']);
        }
        return $this;
    }

    /**
     * Gets the min-col attribute for this query.
     *
     * @return string min-col
     */
    public function getMinCol()
    {
        if (array_key_exists('min-col', $this->_params)) {
            return $this->_params['min-col'];
        } else {
            return null;
        }
    }

    /**
     * Sets the max-col attribute for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setMaxCol($value)
    {
        if ($value != null) {
            $this->_params['max-col'] = $value;
        } else {
            unset($this->_params['max-col']);
        }
        return $this;
    }

    /**
     * Gets the max-col attribute for this query.
     *
     * @return string max-col
     */
    public function getMaxCol()
    {
        if (array_key_exists('max-col', $this->_params)) {
            return $this->_params['max-col'];
        } else {
            return null;
        }
    }

    /**
     * Sets the range attribute for this query.
     *
     * @param string $value
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setRange($value)
    {
        if ($value != null) {
            $this->_params['range'] = $value;
        } else {
            unset($this->_params['range']);
        }
        return $this;
    }

    /**
     * Gets the range attribute for this query.
     *
     * @return string range
     */
    public function getRange()
    {
        if (array_key_exists('range', $this->_params)) {
            return $this->_params['range'];
        } else {
            return null;
        }
    }

    /**
     * Sets the return-empty attribute for this query.
     *
     * @param mixed $value String or bool value for whether to return empty cells
     * @return Zend_Gdata_Spreadsheets_CellQuery Provides a fluent interface
     */
    public function setReturnEmpty($value)
    {
        if (is_bool($value)) {
            $this->_params['return-empty'] = ($value?'true':'false');
        } else if ($value != null) {
            $this->_params['return-empty'] = $value;
        } else {
            unset($this->_params['return-empty']);
        }
        return $this;
    }

    /**
     * Gets the return-empty attribute for this query.
     *
     * @return string return-empty
     */
    public function getReturnEmpty()
    {
        if (array_key_exists('return-empty', $this->_params)) {
            return $this->_params['return-empty'];
        } else {
            return null;
        }
    }

    /**
     * Gets the full query URL for this query.
     *
     * @return string url
     */
    public function getQueryUrl()
    {
        if ($this->_url == null) {
            $uri = $this->_defaultFeedUri;

            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A spreadsheet key must be provided for cell queries.');
            }

            if ($this->_worksheetId != null) {
                $uri .= '/'.$this->_worksheetId;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A worksheet id must be provided for cell queries.');
            }

            if ($this->_visibility != null) {
                $uri .= '/'.$this->_visibility;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A visibility must be provided for cell queries.');
            }

            if ($this->_projection != null) {
                $uri .= '/'.$this->_projection;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A projection must be provided for cell queries.');
            }

            if ($this->_cellId != null) {
                $uri .= '/'.$this->_cellId;
            }
        } else {
            $uri = $this->_url;
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

    /**
     * Gets the attribute query string for this query.
     *
     * @return string query string
     */
    public function getQueryString()
    {
        return parent::getQueryString();
    }

}
