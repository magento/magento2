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
 * @category     Zend
 * @package      Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata
 */
#require_once('Zend/Gdata.php');

/**
 * Zend_Gdata_Spreadsheets_SpreadsheetFeed
 */
#require_once('Zend/Gdata/Spreadsheets/SpreadsheetFeed.php');

/**
 * Zend_Gdata_Spreadsheets_WorksheetFeed
 */
#require_once('Zend/Gdata/Spreadsheets/WorksheetFeed.php');

/**
 * Zend_Gdata_Spreadsheets_CellFeed
 */
#require_once('Zend/Gdata/Spreadsheets/CellFeed.php');

/**
 * Zend_Gdata_Spreadsheets_ListFeed
 */
#require_once('Zend/Gdata/Spreadsheets/ListFeed.php');

/**
 * Zend_Gdata_Spreadsheets_SpreadsheetEntry
 */
#require_once('Zend/Gdata/Spreadsheets/SpreadsheetEntry.php');

/**
 * Zend_Gdata_Spreadsheets_WorksheetEntry
 */
#require_once('Zend/Gdata/Spreadsheets/WorksheetEntry.php');

/**
 * Zend_Gdata_Spreadsheets_CellEntry
 */
#require_once('Zend/Gdata/Spreadsheets/CellEntry.php');

/**
 * Zend_Gdata_Spreadsheets_ListEntry
 */
#require_once('Zend/Gdata/Spreadsheets/ListEntry.php');

/**
 * Zend_Gdata_Spreadsheets_DocumentQuery
 */
#require_once('Zend/Gdata/Spreadsheets/DocumentQuery.php');

/**
 * Zend_Gdata_Spreadsheets_ListQuery
 */
#require_once('Zend/Gdata/Spreadsheets/ListQuery.php');

/**
 * Zend_Gdata_Spreadsheets_CellQuery
 */
#require_once('Zend/Gdata/Spreadsheets/CellQuery.php');

/**
 * Gdata Spreadsheets
 *
 * @link http://code.google.com/apis/gdata/spreadsheets.html
 *
 * @category     Zend
 * @package      Zend_Gdata
 * @subpackage   Spreadsheets
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets extends Zend_Gdata
{
    const SPREADSHEETS_FEED_URI = 'https://spreadsheets.google.com/feeds/spreadsheets';
    const SPREADSHEETS_POST_URI = 'https://spreadsheets.google.com/feeds/spreadsheets/private/full';
    const WORKSHEETS_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#worksheetsfeed';
    const LIST_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#listfeed';
    const CELL_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#cellsfeed';
    const AUTH_SERVICE_NAME = 'wise';

    /**
     * Namespaces used for Zend_Gdata_Photos
     *
     * @var array
     */
    public static $namespaces = array(
        array('gs', 'http://schemas.google.com/spreadsheets/2006', 1, 0),
        array(
            'gsx', 'http://schemas.google.com/spreadsheets/2006/extended', 1, 0)
    );

    /**
     * Create Gdata_Spreadsheets object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Spreadsheets');
        $this->registerPackage('Zend_Gdata_Spreadsheets_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
        $this->_server = 'spreadsheets.google.com';
    }

    /**
     * Gets a spreadsheet feed.
     *
     * @param mixed $location A DocumentQuery or a string URI specifying the feed location.
     * @return Zend_Gdata_Spreadsheets_SpreadsheetFeed
     */
    public function getSpreadsheetFeed($location = null)
    {
        if ($location == null) {
            $uri = self::SPREADSHEETS_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('spreadsheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_SpreadsheetFeed');
    }

    /**
     * Gets a spreadsheet entry.
     *
     * @param string $location A DocumentQuery or a URI specifying the entry location.
     * @return SpreadsheetEntry
     */
    public function getSpreadsheetEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('spreadsheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_SpreadsheetEntry');
    }

    /**
     * Gets a worksheet feed.
     *
     * @param mixed $location A DocumentQuery, SpreadsheetEntry, or a string URI
     * @return Zend_Gdata_Spreadsheets_WorksheetFeed The feed of worksheets
     */
    public function getWorksheetFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('worksheets');
            }
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_SpreadsheetEntry) {
            $uri = $location->getLink(self::WORKSHEETS_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_WorksheetFeed');
    }

    /**
     * Gets a worksheet entry.
     *
     * @param string $location A DocumentQuery or a URI specifying the entry location.
     * @return WorksheetEntry
     */
    public function GetWorksheetEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('worksheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_WorksheetEntry');
    }

    /**
     * Gets a cell feed.
     *
     * @param string $location A CellQuery, WorksheetEntry or a URI specifying the feed location.
     * @return CellFeed
     */
    public function getCellFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $uri = $location->getLink(self::CELL_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_CellFeed');
    }

    /**
     * Gets a cell entry.
     *
     * @param string $location A CellQuery or a URI specifying the entry location.
     * @return CellEntry
     */
    public function getCellEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_CellEntry');
    }

    /**
     * Gets a list feed.
     *
     * @param mixed $location A ListQuery, WorksheetEntry or string URI specifying the feed location.
     * @return ListFeed
     */
    public function getListFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_ListQuery) {
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $uri = $location->getLink(self::LIST_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_ListFeed');
    }

    /**
     * Gets a list entry.
     *
     * @param string $location A ListQuery or a URI specifying the entry location.
     * @return ListEntry
     */
    public function getListEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_ListQuery) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_ListEntry');
    }

    /**
     * Updates an existing cell.
     *
     * @param int $row The row containing the cell to update
     * @param int $col The column containing the cell to update
     * @param int $inputValue The new value for the cell
     * @param string $key The key for the spreadsheet to be updated
     * @param string $wkshtId (optional) The worksheet to be updated
     * @return CellEntry The updated cell entry.
     */
    public function updateCell($row, $col, $inputValue, $key, $wkshtId = 'default')
    {
        $cell = 'R'.$row.'C'.$col;

        $query = new Zend_Gdata_Spreadsheets_CellQuery();
        $query->setSpreadsheetKey($key);
        $query->setWorksheetId($wkshtId);
        $query->setCellId($cell);

        $entry = $this->getCellEntry($query);
        $entry->setCell(new Zend_Gdata_Spreadsheets_Extension_Cell(null, $row, $col, $inputValue));
        $response = $entry->save();
        return $response;
    }

    /**
     * Inserts a new row with provided data.
     *
     * @param array $rowData An array of column header to row data
     * @param string $key The key of the spreadsheet to modify
     * @param string $wkshtId (optional) The worksheet to modify
     * @return ListEntry The inserted row
     */
    public function insertRow($rowData, $key, $wkshtId = 'default')
    {
        $newEntry = new Zend_Gdata_Spreadsheets_ListEntry();
        $newCustomArr = array();
        foreach ($rowData as $k => $v) {
            $newCustom = new Zend_Gdata_Spreadsheets_Extension_Custom();
            $newCustom->setText($v)->setColumnName($k);
            $newEntry->addCustom($newCustom);
        }

        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($key);
        $query->setWorksheetId($wkshtId);

        $feed = $this->getListFeed($query);
        $editLink = $feed->getLink('http://schemas.google.com/g/2005#post');

        return $this->insertEntry($newEntry->saveXML(), $editLink->href, 'Zend_Gdata_Spreadsheets_ListEntry');
    }

    /**
     * Updates an existing row with provided data.
     *
     * @param ListEntry $entry The row entry to update
     * @param array $newRowData An array of column header to row data
     */
    public function updateRow($entry, $newRowData)
    {
        $newCustomArr = array();
        foreach ($newRowData as $k => $v) {
            $newCustom = new Zend_Gdata_Spreadsheets_Extension_Custom();
            $newCustom->setText($v)->setColumnName($k);
            $newCustomArr[] = $newCustom;
        }
        $entry->setCustom($newCustomArr);

        return $entry->save();
    }

    /**
     * Deletes an existing row .
     *
     * @param ListEntry $entry The row to delete
     */
    public function deleteRow($entry)
    {
        $entry->delete();
    }

    /**
     * Returns the content of all rows as an associative array
     *
     * @param mixed $location A ListQuery or string URI specifying the feed location.
     * @return array An array of rows.  Each element of the array is an associative array of data
     */
    public function getSpreadsheetListFeedContents($location)
    {
        $listFeed = $this->getListFeed($location);
        $listFeed = $this->retrieveAllEntriesForFeed($listFeed);
        $spreadsheetContents = array();
        foreach ($listFeed as $listEntry) {
            $rowContents = array();
            $customArray = $listEntry->getCustom();
            foreach ($customArray as $custom) {
                $rowContents[$custom->getColumnName()] = $custom->getText();
            }
            $spreadsheetContents[] = $rowContents;
        }
        return $spreadsheetContents;
    }

    /**
     * Returns the content of all cells as an associative array, indexed
     * off the cell location  (ie 'A1', 'D4', etc).  Each element of
     * the array is an associative array with a 'value' and a 'function'.
     * Only non-empty cells are returned by default.  'range' is the
     * value of the 'range' query parameter specified at:
     * http://code.google.com/apis/spreadsheets/reference.html#cells_Parameters
     *
     * @param mixed $location A CellQuery, WorksheetEntry or a URL (w/o query string) specifying the feed location.
     * @param string $range The range of cells to retrieve
     * @param boolean $empty Whether to retrieve empty cells
     * @return array An associative array of cells
     */
    public function getSpreadsheetCellFeedContents($location, $range = null, $empty = false)
    {
        $cellQuery = null;
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $cellQuery = $location;
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $url = $location->getLink(self::CELL_FEED_LINK_URI)->href;
            $cellQuery = new Zend_Gdata_Spreadsheets_CellQuery($url);
        } else {
            $url = $location;
            $cellQuery = new Zend_Gdata_Spreadsheets_CellQuery($url);
        }

        if ($range != null) {
            $cellQuery->setRange($range);
        }
        $cellQuery->setReturnEmpty($empty);

        $cellFeed = $this->getCellFeed($cellQuery);
        $cellFeed = $this->retrieveAllEntriesForFeed($cellFeed);
        $spreadsheetContents = array();
        foreach ($cellFeed as $cellEntry) {
            $cellContents = array();
            $cell = $cellEntry->getCell();
            $cellContents['formula'] = $cell->getInputValue();
            $cellContents['value'] = $cell->getText();
            $spreadsheetContents[$cellEntry->getTitle()->getText()] = $cellContents;
        }
        return $spreadsheetContents;
    }

    /**
     * Alias for getSpreadsheetFeed
     *
     * @param mixed $location A DocumentQuery or a string URI specifying the feed location.
     * @return Zend_Gdata_Spreadsheets_SpreadsheetFeed
     */
    public function getSpreadsheets($location = null)
    {
        return $this->getSpreadsheetFeed($location = null);
    }

}
