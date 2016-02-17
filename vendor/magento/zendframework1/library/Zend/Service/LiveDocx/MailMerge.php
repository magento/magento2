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
 * @package    Zend_Service
 * @subpackage LiveDocx
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Date **/
#require_once 'Zend/Date.php';

/** Zend_Service_LiveDocx **/
#require_once 'Zend/Service/LiveDocx.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage LiveDocx
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @since      LiveDocx 1.0
 */
class Zend_Service_LiveDocx_MailMerge extends Zend_Service_LiveDocx
{
    /**
     * URI of LiveDocx.MailMerge WSDL
     * @since LiveDocx 1.0
     */
    //const WSDL = 'https://api.livedocx.com/1.2/mailmerge.asmx?WSDL';
    const WSDL = 'https://api.livedocx.com/2.0/mailmerge.asmx?WSDL';

    /**
     * Field values
     *
     * @var   array
     * @since LiveDocx 1.0
     */
    protected $_fieldValues;

    /**
     * Block field values
     *
     * @var   array
     * @since LiveDocx 1.0
     */
    protected $_blockFieldValues;

    /**
     * Constructor (LiveDocx.MailMerge SOAP Service)
     *
     * @return void
     * @return throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function __construct($options = null)
    {
        $this->_wsdl             = self::WSDL;
        $this->_fieldValues      = array();
        $this->_blockFieldValues = array();

        parent::__construct($options);
    }

    /**
     * Set the filename of a LOCAL template
     * (i.e. a template stored locally on YOUR server)
     *
     * @param  string $filename
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function setLocalTemplate($filename)
    {
        if (!is_readable($filename)) {
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot read local template from disk.'
            );
        }

        $this->logIn();

        try {
            $this->getSoapClient()->SetLocalTemplate(array(
                'template' => base64_encode(file_get_contents($filename)),
                'format'   => self::getFormat($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set local template', 0, $e
            );
        }

        return $this;
    }

    /**
     * Set the filename of a REMOTE template
     * (i.e. a template stored remotely on the LIVEDOCX server)
     *
     * @param  string $filename
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function setRemoteTemplate($filename)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->SetRemoteTemplate(array(
                'filename' => $filename,
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set remote template', 0, $e
            );
        }

        return $this;
    }

    /**
     * Set an associative or multi-associative array of keys and values pairs
     *
     * @param  array $values
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function setFieldValues($values)
    {
        $this->logIn();

        foreach ($values as $value) {
            if (is_array($value)) {
                $method = 'multiAssocArrayToArrayOfArrayOfString';
            } else {
                $method = 'assocArrayToArrayOfArrayOfString';
            }
            break;
        }

        try {
            $this->getSoapClient()->SetFieldValues(array(
                'fieldValues' => self::$method($values),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set field values', 0, $e
            );
        }

        return $this;
    }

    /**
     * Set an array of key and value or array of values
     *
     * @param string $field
     * @param array|string $value
     *
     * @throws Zend_Service_LiveDocx_Exception
     * @return Zend_Service_LiveDocx_MailMerge
     * @since  LiveDocx 1.0
     */
    public function setFieldValue($field, $value)
    {
        $this->_fieldValues[$field] = $value;

        return $this;
    }

    /**
     * Set block field values
     *
     * @param string $blockName
     * @param array $blockFieldValues
     *
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function setBlockFieldValues($blockName, $blockFieldValues)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->SetBlockFieldValues(array(
                'blockName'        => $blockName,
                'blockFieldValues' => self::multiAssocArrayToArrayOfArrayOfString($blockFieldValues)
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set block field values', 0, $e
            );
        }

        return $this;
    }

    /**
     * Assign values to template fields
     *
     * @param array|string $field
     * @param array|string $value
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function assign($field, $value = null)
    {
        try {
            if (is_array($field) && (null === $value)) {
                foreach ($field as $fieldName => $fieldValue) {
                    $this->setFieldValue($fieldName, $fieldValue);
                }
            } elseif (is_array($value)) {
                $this->setBlockFieldValues($field, $value);
            } else {
                $this->setFieldValue($field, $value);
            }
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot assign data to template', 0, $e
            );
        }

        return $this;
    }

    /**
     * Set a password to open to document
     *
     * This method can only be used for PDF documents
     *
     * @param  string  $password
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.2 Premium
     */
    public function setDocumentPassword($password)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->SetDocumentPassword(array(
                'password' => $password
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set document password. This method can be used on PDF files only.', 0, $e
            );
        }

        return $this;
    }

    /**
     * Set a master password for document and determine which security features
     * are accessible without using the master password.
     *
     * As default, nothing is allowed. To allow a security setting,
     * explicatively set it using one of he DOCUMENT_ACCESS_PERMISSION_* class
     * constants.
     *
     * {code}
     * $phpLiveDocx->setDocumentAccessPermissions(
     *     array (
     *         Zend_Service_LiveDocx_MailMerge::DOCUMENT_ACCESS_PERMISSION_ALLOW_PRINTING_HIGH_LEVEL,
     *         Zend_Service_LiveDocx_MailMerge::DOCUMENT_ACCESS_PERMISSION_ALLOW_EXTRACT_CONTENTS
     *     ),
     *     'myDocumentAccessPassword'
     * );
     * {code}
     *
     * This method can only be used for PDF documents
     *
     * @param  array  $permissions
     * @param  string $password
     * @return Zend_Service_LiveDocx_MailMerge
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.2 Premium
     */
    public function setDocumentAccessPermissions($permissions, $password)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->SetDocumentAccessPermissions(array(
                'permissions' => $permissions,
                'password'    => $password
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot set document access permissions', 0, $e
            );
        }

        return $this;
    }

    /**
     * Merge assigned data with template to generate document
     *
     * @throws Zend_Service_LiveDocx_Excpetion
     * @return void
     * @since  LiveDocx 1.0
     */
    public function createDocument()
    {
        $this->logIn();

        if (count($this->_fieldValues) > 0) {
            $this->setFieldValues($this->_fieldValues);
        }

        $this->_fieldValues      = array();
        $this->_blockFieldValues = array();

        try {
            $this->getSoapClient()->CreateDocument();
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot create document', 0, $e
            );
        }
    }

    /**
     * Retrieve document in specified format
     *
     * @param string $format
     *
     * @throws Zend_Service_LiveDocx_Exception
     * @return binary
     * @since  LiveDocx 1.0
     */
    public function retrieveDocument($format)
    {
        $this->logIn();

        $format = strtolower($format);

        try {
            $result = $this->getSoapClient()->RetrieveDocument(array(
                'format' => $format,
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot retrieve document - call setLocalTemplate() or setRemoteTemplate() first', 0, $e
            );
        }

        return base64_decode($result->RetrieveDocumentResult);
    }

    /**
     * Return WMF (aka Windows metafile) data for specified page range of created document
     * Return array contains WMF data (binary) - array key is page number
     *
     * @param  integer $fromPage
     * @param  integer $toPage
     * @return array
     * @since  LiveDocx 1.2
     */
    public function getMetafiles($fromPage, $toPage)
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetMetafiles(array(
            'fromPage' => (integer) $fromPage,
            'toPage'   => (integer) $toPage,
        ));

        if (isset($result->GetMetafilesResult->string)) {
            $pageCounter = (integer) $fromPage;
            if (is_array($result->GetMetafilesResult->string)) {
                foreach ($result->GetMetafilesResult->string as $string) {
                    $ret[$pageCounter] = base64_decode($string);
                    $pageCounter++;
                }
            } else {
               $ret[$pageCounter] = base64_decode($result->GetMetafilesResult->string);
            }
        }

        return $ret;
    }

    /**
     * Return WMF (aka Windows metafile) data for pages of created document
     * Return array contains WMF data (binary) - array key is page number
     *
     * @return array
     * @since  LiveDocx 1.2
     */
    public function getAllMetafiles()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetAllMetafiles();

        if (isset($result->GetAllMetafilesResult->string)) {
            $pageCounter = 1;
            if (is_array($result->GetAllMetafilesResult->string)) {
                foreach ($result->GetAllMetafilesResult->string as $string) {
                    $ret[$pageCounter] = base64_decode($string);
                    $pageCounter++;
                }
            } else {
               $ret[$pageCounter] = base64_decode($result->GetAllMetafilesResult->string);
            }
        }

        return $ret;
    }

    /**
     * Return graphical bitmap data for specified page range of created document
     * Return array contains bitmap data (binary) - array key is page number
     *
     * @param  integer $fromPage
     * @param  integer $toPage
     * @param  integer $zoomFactor
     * @param  string  $format
     * @return array
     * @since  LiveDocx 1.2
     */
    public function getBitmaps($fromPage, $toPage, $zoomFactor, $format)
    {
        $this->logIn();

        $ret = array();

        $result = $this->getSoapClient()->GetBitmaps(array(
            'fromPage'   => (integer) $fromPage,
            'toPage'     => (integer) $toPage,
            'zoomFactor' => (integer) $zoomFactor,
            'format'     => (string)  $format,
        ));

        if (isset($result->GetBitmapsResult->string)) {
            $pageCounter = (integer) $fromPage;
            if (is_array($result->GetBitmapsResult->string)) {
                foreach ($result->GetBitmapsResult->string as $string) {
                    $ret[$pageCounter] = base64_decode($string);
                    $pageCounter++;
                }
            } else {
               $ret[$pageCounter] = base64_decode($result->GetBitmapsResult->string);
            }
        }

        return $ret;
    }

    /**
     * Return graphical bitmap data for all pages of created document
     * Return array contains bitmap data (binary) - array key is page number
     *
     * @param  integer $zoomFactor
     * @param  string  $format
     * @return array
     * @since  LiveDocx 1.2
     */
    public function getAllBitmaps($zoomFactor, $format)
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetAllBitmaps(array(
            'zoomFactor' => (integer) $zoomFactor,
            'format'     => (string)  $format,
        ));

        if (isset($result->GetAllBitmapsResult->string)) {
            $pageCounter = 1;
            if (is_array($result->GetAllBitmapsResult->string)) {
                foreach ($result->GetAllBitmapsResult->string as $string) {
                    $ret[$pageCounter] = base64_decode($string);
                    $pageCounter++;
                }
            } else {
               $ret[$pageCounter] = base64_decode($result->GetAllBitmapsResult->string);
            }
        }

        return $ret;
    }

    /**
     * Return all the fields in the template
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public function getFieldNames()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetFieldNames();

        if (isset($result->GetFieldNamesResult->string)) {
            if (is_array($result->GetFieldNamesResult->string)) {
                $ret = $result->GetFieldNamesResult->string;
            } else {
                $ret[] = $result->GetFieldNamesResult->string;
            }
        }

        return $ret;
    }

    /**
     * Return all the block fields in the template
     *
     * @param  string $blockName
     * @return array
     * @since  LiveDocx 1.0
     */
    public function getBlockFieldNames($blockName)
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetBlockFieldNames(array(
            'blockName' => $blockName
        ));

        if (isset($result->GetBlockFieldNamesResult->string)) {
            if (is_array($result->GetBlockFieldNamesResult->string)) {
                $ret = $result->GetBlockFieldNamesResult->string;
            } else {
                $ret[] = $result->GetBlockFieldNamesResult->string;
            }
        }

        return $ret;
    }

    /**
     * Return all the block fields in the template
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public function getBlockNames()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetBlockNames();

        if (isset($result->GetBlockNamesResult->string)) {
            if (is_array($result->GetBlockNamesResult->string)) {
                $ret = $result->GetBlockNamesResult->string;
            } else {
                $ret[] = $result->GetBlockNamesResult->string;
            }
        }

        return $ret;
    }

    /**
     * Upload a template file to LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function uploadTemplate($filename)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->UploadTemplate(array(
                'template' => base64_encode(file_get_contents($filename)),
                'filename' => basename($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot upload template', 0, $e
            );
        }
    }

    /**
     * Download template file from LiveDocx service
     *
     * @param  string $filename
     * @return binary
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function downloadTemplate($filename)
    {
        $this->logIn();

        try {
            $result = $this->getSoapClient()->DownloadTemplate(array(
                'filename' => basename($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot download template', 0, $e
            );
        }

        return base64_decode($result->DownloadTemplateResult);
    }

    /**
     * Delete a template file from LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function deleteTemplate($filename)
    {
        $this->logIn();

        $this->getSoapClient()->DeleteTemplate(array(
            'filename' => basename($filename),
        ));
    }

    /**
     * List all templates stored on LiveDocx service
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public function listTemplates()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->ListTemplates();

        if (isset($result->ListTemplatesResult)) {
            $ret = $this->_backendListArrayToMultiAssocArray($result->ListTemplatesResult);
        }

        return $ret;
    }

    /**
     * Check whether a template file is available on LiveDocx service
     *
     * @param  string $filename
     * @return boolean
     * @since  LiveDocx 1.0
     */
    public function templateExists($filename)
    {
        $this->logIn();

        $result = $this->getSoapClient()->TemplateExists(array(
            'filename' => basename($filename),
        ));

        return (boolean) $result->TemplateExistsResult;
    }

    /**
     * Share a document - i.e. the document is available to all over the Internet
     *
     * @return string
     * @since  LiveDocx 1.0
     */
    public function shareDocument()
    {
        $this->logIn();

        $ret    = null;
        $result = $this->getSoapClient()->ShareDocument();

        if (isset($result->ShareDocumentResult)) {
            $ret = (string) $result->ShareDocumentResult;
        }

        return $ret;
    }

    /**
     * List all shared documents stored on LiveDocx service
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public function listSharedDocuments()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->ListSharedDocuments();

        if (isset($result->ListSharedDocumentsResult)) {
            $ret = $this->_backendListArrayToMultiAssocArray(
                $result->ListSharedDocumentsResult
            );
        }

        return $ret;
    }

    /**
     * Delete a shared document from LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @since  LiveDocx 1.0
     */
    public function deleteSharedDocument($filename)
    {
        $this->logIn();

        $this->getSoapClient()->DeleteSharedDocument(array(
            'filename' => basename($filename),
        ));
    }

    /*
     * Download a shared document from LiveDocx service
     *
     * @param  string $filename
     * @return binary
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */
    public function downloadSharedDocument($filename)
    {
        $this->logIn();

        try {
            $result = $this->getSoapClient()->DownloadSharedDocument(array(
                'filename' => basename($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot download shared document', 0, $e
            );
        }

        return base64_decode($result->DownloadSharedDocumentResult);
    }

    /**
     * Check whether a shared document is available on LiveDocx service
     *
     * @param  string $filename
     * @return boolean
     * @since  LiveDocx 1.0
     */
    public function sharedDocumentExists($filename)
    {
        $this->logIn();

        $ret             = false;
        $sharedDocuments = $this->listSharedDocuments();
        foreach ($sharedDocuments as $shareDocument) {
            if (isset($shareDocument['filename'])
                && (basename($filename) === $shareDocument['filename'])
            ) {
                $ret = true;
                break;
            }
        }

        return $ret;
    }

    /**
     * Return supported template formats (lowercase)
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public function getTemplateFormats()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetTemplateFormats();

        if (isset($result->GetTemplateFormatsResult->string)) {
            $ret = $result->GetTemplateFormatsResult->string;
            $ret = array_map('strtolower', $ret);
        }

        return $ret;
    }

    /**
     * Return supported document formats (lowercase)
     *
     * @return array
     * @since  LiveDocx 1.1
     */
    public function getDocumentFormats()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetDocumentFormats();

        if (isset($result->GetDocumentFormatsResult->string)) {
            $ret = $result->GetDocumentFormatsResult->string;
            $ret = array_map('strtolower', $ret);
        }

        return $ret;
    }

    /**
     * Return the names of all fonts that are installed on backend server
     *
     * @return array
     * @since  LiveDocx 1.2
     */
    public function getFontNames()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetFontNames();

        if (isset($result->GetFontNamesResult->string)) {
            $ret = $result->GetFontNamesResult->string;
        }

        return $ret;
    }

    /**
     * Return supported document access options
     *
     * @return array
     * @since  LiveDocx 1.2 Premium
     */
    public function getDocumentAccessOptions()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetDocumentAccessOptions();

        if (isset($result->GetDocumentAccessOptionsResult->string)) {
            $ret = $result->GetDocumentAccessOptionsResult->string;
        }

        return $ret;
    }

    /**
     * Return supported image formats from which can be imported (lowercase)
     *
     * @return array
     * @since  LiveDocx 2.0
     */
    public function getImageImportFormats()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetImageImportFormats();

        if (isset($result->GetImageImportFormatsResult->string)) {
            $ret = $result->GetImageImportFormatsResult->string;
            $ret = array_map('strtolower', $ret);
        }

        return $ret;
    }

    /**
     * Return supported image formats to which can be exported (lowercase)
     *
     * @return array
     * @since  LiveDocx 2.0
     */
    public function getImageExportFormats()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->GetImageExportFormats();

        if (isset($result->GetImageExportFormatsResult->string)) {
            $ret = $result->GetImageExportFormatsResult->string;
            $ret = array_map('strtolower', $ret);
        }

        return $ret;
    }

    /*
     * Return supported image formats (lowercase)
     *
     * @return array
     * @since  LiveDocx 1.2
     * @deprecated since LiveDocx 2.0
     */
    public function getImageFormats()
    {
        $replacement = 'getImageExportFormats';

        /*
        $errorMessage = sprintf(
                        "%s::%s is deprecated as of LiveDocx 2.0. "
                      . "It has been replaced by %s::%s() (drop in replacement)",
                        __CLASS__, __FUNCTION__, __CLASS__, $replacement);

        trigger_error($errorMessage, E_USER_NOTICE);
        */

        return $this->$replacement();
    }

    /**
     * Upload an image file to LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 2.0
     */
    public function uploadImage($filename)
    {
        $this->logIn();

        try {
            $this->getSoapClient()->UploadImage(array(
                'image'    => base64_encode(file_get_contents($filename)),
                'filename' => basename($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot upload image', 0, $e
            );
        }
    }

    /**
     * Download an image file from LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 2.0
     */
    public function downloadImage($filename)
    {
        $this->logIn();

        try {
            $result = $this->getSoapClient()->DownloadImage(array(
                'filename' => basename($filename),
            ));
        } catch (Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception(
                'Cannot download image', 0, $e
            );
        }

        return base64_decode($result->DownloadImageResult);
    }

    /**
     * List all images stored on LiveDocx service
     *
     * @return array
     * @since  LiveDocx 2.0
     */
    public function listImages()
    {
        $this->logIn();

        $ret    = array();
        $result = $this->getSoapClient()->ListImages();

        if (isset($result->ListImagesResult)) {
            $ret = $this->_backendListArrayToMultiAssocArray($result->ListImagesResult);
        }

        return $ret;
    }

    /**
     * Delete an image file from LiveDocx service
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 2.0
     */
    public function deleteImage($filename)
    {
        $this->logIn();

        $this->getSoapClient()->DeleteImage(array(
            'filename' => basename($filename),
        ));
    }

    /**
     * Check whether an image file is available on LiveDocx service
     *
     * @param  string $filename
     * @return boolean
     * @since  LiveDocx 2.0
     */
    public function imageExists($filename)
    {
        $this->logIn();

        $result = $this->getSoapClient()->ImageExists(array(
            'filename' => basename($filename),
        ));

        return (boolean) $result->ImageExistsResult;
    }

    /**
     * Convert LiveDocx service return value from list methods to consistent PHP array
     *
     * @param  array $list
     * @return array
     * @since  LiveDocx 1.0
     */
    protected function _backendListArrayToMultiAssocArray($list)
    {
        $this->logIn();

        $ret = array();
        if (isset($list->ArrayOfString)) {
           foreach ($list->ArrayOfString as $a) {
               if (is_array($a)) {      // 1 template only
                   $o = new stdClass();
                   $o->string = $a;
               } else {                 // 2 or more templates
                   $o = $a;
               }
               unset($a);

               if (isset($o->string)) {
                   $date1 = new Zend_Date($o->string[3], Zend_Date::RFC_1123);
                   $date2 = new Zend_Date($o->string[1], Zend_Date::RFC_1123);

                   $ret[] = array (
                        'filename'   => $o->string[0],
                        'fileSize'   => (integer) $o->string[2],
                        'createTime' => (integer) $date1->get(Zend_Date::TIMESTAMP),
                        'modifyTime' => (integer) $date2->get(Zend_Date::TIMESTAMP),
                   );
               }
           }
        }

        return $ret;
    }

    /**
     * Convert assoc array to required SOAP type
     *
     * @param array $assoc
     *
     * @return array
     * @since  LiveDocx 1.0
     */
    public static function assocArrayToArrayOfArrayOfString($assoc)
    {
        $arrayKeys   = array_keys($assoc);
        $arrayValues = array_values($assoc);

        return array($arrayKeys, $arrayValues);
    }

    /**
     * Convert multi assoc array to required SOAP type
     *
     * @param  array $multi
     * @return array
     * @since  LiveDocx 1.0
     */
    public static function multiAssocArrayToArrayOfArrayOfString($multi)
    {
        $arrayKeys   = array_keys($multi[0]);
        $arrayValues = array();

        foreach ($multi as $v) {
            $arrayValues[] = array_values($v);
        }

        $arrayKeys = array($arrayKeys);

        return array_merge($arrayKeys, $arrayValues);
    }

    // -------------------------------------------------------------------------

}
