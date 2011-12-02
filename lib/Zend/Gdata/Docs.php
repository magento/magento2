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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Docs.php 20112 2010-01-07 02:39:32Z tjohns $
 */

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_Docs_DocumentListFeed
 */
#require_once 'Zend/Gdata/Docs/DocumentListFeed.php';

/**
 * @see Zend_Gdata_Docs_DocumentListEntry
 */
#require_once 'Zend/Gdata/Docs/DocumentListEntry.php';

/**
 * @see Zend_Gdata_App_Extension_Category
 */
#require_once 'Zend/Gdata/App/Extension/Category.php';

/**
 * @see Zend_Gdata_App_Extension_Title
 */
#require_once 'Zend/Gdata/App/Extension/Title.php';

/**
 * Service class for interacting with the Google Document List data API
 * @link http://code.google.com/apis/documents/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Docs
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Docs extends Zend_Gdata
{

    const DOCUMENTS_LIST_FEED_URI = 'http://docs.google.com/feeds/documents/private/full';
    const DOCUMENTS_FOLDER_FEED_URI = 'http://docs.google.com/feeds/folders/private/full';
    const DOCUMENTS_CATEGORY_SCHEMA = 'http://schemas.google.com/g/2005#kind';
    const DOCUMENTS_CATEGORY_TERM = 'http://schemas.google.com/docs/2007#folder';
    const AUTH_SERVICE_NAME = 'writely';

    protected $_defaultPostUri = self::DOCUMENTS_LIST_FEED_URI;

    private static $SUPPORTED_FILETYPES = array(
      'TXT'=>'text/plain',
      'CSV'=>'text/csv',
      'TSV'=>'text/tab-separated-values',
      'TAB'=>'text/tab-separated-values',
      'HTML'=>'text/html',
      'HTM'=>'text/html',
      'DOC'=>'application/msword',
      'ODS'=>'application/vnd.oasis.opendocument.spreadsheet',
      'ODT'=>'application/vnd.oasis.opendocument.text',
      'RTF'=>'application/rtf',
      'SXW'=>'application/vnd.sun.xml.writer',
      'XLS'=>'application/vnd.ms-excel',
      'XLSX'=>'application/vnd.ms-excel',
      'PPT'=>'application/vnd.ms-powerpoint',
      'PPS'=>'application/vnd.ms-powerpoint');

    /**
     * Create Gdata_Docs object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Docs');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    /**
     * Looks up the mime type based on the file name extension. For example,
     * calling this method with 'csv' would return
     * 'text/comma-separated-values'. The Mime type is sent as a header in
     * the upload HTTP POST request.
     *
     * @param string $fileExtension
     * @return string The mime type to be sent to the server to tell it how the
     *          multipart mime data should be interpreted.
     */
    public static function lookupMimeType($fileExtension) {
      return self::$SUPPORTED_FILETYPES[strtoupper($fileExtension)];
    }

    /**
     * Retreive feed object containing entries for the user's documents.
     *
     * @param mixed $location The location for the feed, as a URL or Query
     * @return Zend_Gdata_Docs_DocumentListFeed
     */
    public function getDocumentListFeed($location = null)
    {
        if ($location === null) {
            $uri = self::DOCUMENTS_LIST_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Docs_DocumentListFeed');
    }

    /**
     * Retreive entry object representing a single document.
     *
     * @param mixed $location The location for the entry, as a URL or Query
     * @return Zend_Gdata_Docs_DocumentListEntry
     */
    public function getDocumentListEntry($location = null)
    {
        if ($location === null) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Docs_DocumentListEntry');
    }

    /**
     * Retreive entry object representing a single document.
     *
     * This method builds the URL where this item is stored using the type
     * and the id of the document.
     * @param string $docId The URL key for the document. Examples:
     *     dcmg89gw_62hfjj8m, pKq0CzjiF3YmGd0AIlHKqeg
     * @param string $docType The type of the document as used in the Google
     *     Document List URLs. Examples: document, spreadsheet, presentation
     * @return Zend_Gdata_Docs_DocumentListEntry
     */
    public function getDoc($docId, $docType) {
        $location = 'http://docs.google.com/feeds/documents/private/full/' .
            $docType . '%3A' . $docId;
        return $this->getDocumentListEntry($location);
    }

    /**
     * Retreive entry object for the desired word processing document.
     *
     * @param string $id The URL id for the document. Example:
     *     dcmg89gw_62hfjj8m
     */
    public function getDocument($id) {
      return $this->getDoc($id, 'document');
    }

    /**
     * Retreive entry object for the desired spreadsheet.
     *
     * @param string $id The URL id for the document. Example:
     *     pKq0CzjiF3YmGd0AIlHKqeg
     */
    public function getSpreadsheet($id) {
      return $this->getDoc($id, 'spreadsheet');
    }

    /**
     * Retreive entry object for the desired presentation.
     *
     * @param string $id The URL id for the document. Example:
     *     dcmg89gw_21gtrjcn
     */
    public function getPresentation($id) {
      return $this->getDoc($id, 'presentation');
    }

    /**
     * Upload a local file to create a new Google Document entry.
     *
     * @param string $fileLocation The full or relative path of the file to
     *         be uploaded.
     * @param string $title The name that this document should have on the
     *         server. If set, the title is used as the slug header in the
     *         POST request. If no title is provided, the location of the
     *         file will be used as the slug header in the request. If no
     *         mimeType is provided, this method attempts to determine the
     *         mime type based on the slugHeader by looking for .doc,
     *         .csv, .txt, etc. at the end of the file name.
     *         Example value: 'test.doc'.
     * @param string $mimeType Describes the type of data which is being sent
     *         to the server. This must be one of the accepted mime types
     *         which are enumerated in SUPPORTED_FILETYPES.
     * @param string $uri (optional) The URL to which the upload should be
     *         made.
     *         Example: 'http://docs.google.com/feeds/documents/private/full'.
     * @return Zend_Gdata_Docs_DocumentListEntry The entry for the newly
     *         created Google Document.
     */
    public function uploadFile($fileLocation, $title=null, $mimeType=null,
                               $uri=null)
    {
        // Set the URI to which the file will be uploaded.
        if ($uri === null) {
            $uri = $this->_defaultPostUri;
        }

        // Create the media source which describes the file.
        $fs = $this->newMediaFileSource($fileLocation);
        if ($title !== null) {
            $slugHeader = $title;
        } else {
            $slugHeader = $fileLocation;
        }

        // Set the slug header to tell the Google Documents server what the
        // title of the document should be and what the file extension was
        // for the original file.
        $fs->setSlug($slugHeader);

        // Set the mime type of the data.
        if ($mimeType === null) {
          $filenameParts = explode('.', $fileLocation);
          $fileExtension = end($filenameParts);
          $mimeType = self::lookupMimeType($fileExtension);
        }

        // Set the mime type for the upload request.
        $fs->setContentType($mimeType);

        // Send the data to the server.
        return $this->insertDocument($fs, $uri);
    }

    /**
     * Creates a new folder in Google Docs
     *
     * @param string $folderName The folder name to create
     * @param string|null $folderResourceId The parent folder to create it in
     *        ("folder%3Amy_parent_folder")
     * @return Zend_Gdata_Entry The folder entry created.
     * @todo ZF-8732: This should return a *subclass* of Zend_Gdata_Entry, but
     *       the appropriate type doesn't exist yet.
     */
    public function createFolder($folderName, $folderResourceId=null) {
        $category = new Zend_Gdata_App_Extension_Category(self::DOCUMENTS_CATEGORY_TERM, 
                                                          self::DOCUMENTS_CATEGORY_SCHEMA);
        $title = new Zend_Gdata_App_Extension_Title($folderName);
        $entry = new Zend_Gdata_Entry();

        $entry->setCategory(array($category));
        $entry->setTitle($title);

        $uri = self::DOCUMENTS_LIST_FEED_URI;
        if ($folderResourceId != null) {
            $uri = self::DOCUMENTS_FOLDER_FEED_URI . '/' . $folderResourceId;
        }

        return $this->insertEntry($entry, $uri);
    }

    /**
     * Inserts an entry to a given URI and returns the response as an Entry.
     *
     * @param mixed  $data The Zend_Gdata_Docs_DocumentListEntry or media
     *         source to post. If it is a DocumentListEntry, the mediaSource
     *         should already have been set. If $data is a mediaSource, it
     *         should have the correct slug header and mime type.
     * @param string $uri POST URI
     * @param string $className (optional) The class of entry to be returned.
     *         The default is a 'Zend_Gdata_Docs_DocumentListEntry'.
     * @return Zend_Gdata_Docs_DocumentListEntry The entry returned by the
     *     service after insertion.
     */
    public function insertDocument($data, $uri,
        $className='Zend_Gdata_Docs_DocumentListEntry')
    {
        return $this->insertEntry($data, $uri, $className);
    }

}
