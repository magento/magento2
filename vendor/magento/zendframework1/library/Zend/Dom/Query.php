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
 * @package    Zend_Dom
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Dom_Query_Css2Xpath
 */
#require_once 'Zend/Dom/Query/Css2Xpath.php';

/**
 * @see Zend_Dom_Query_Result
 */
#require_once 'Zend/Dom/Query/Result.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/** @see Zend_Xml_Exception */
#require_once 'Zend/Xml/Exception.php';

/**
 * Query DOM structures based on CSS selectors and/or XPath
 *
 * @package    Zend_Dom
 * @subpackage Query
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dom_Query
{
    /**#@+
     * Document types
     */
    const DOC_DOM   = 'docDom';
    const DOC_XML   = 'docXml';
    const DOC_HTML  = 'docHtml';
    const DOC_XHTML = 'docXhtml';
    /**#@-*/

    /**
     * @var string|DOMDocument
     */
    protected $_document;

    /**
     * DOMDocument errors, if any
     * @var false|array
     */
    protected $_documentErrors = false;

    /**
     * Document type
     * @var string
     */
    protected $_docType;

    /**
     * Document encoding
     * @var null|string
     */
    protected $_encoding;

    /**
     * XPath namespaces
     * @var array
     */
    protected $_xpathNamespaces = array();

    /**
     * Constructor
     *
     * @param null|string|DOMDocument $document
     * @param null|string $encoding
     */
    public function __construct($document = null, $encoding = null)
    {
        $this->setEncoding($encoding);
        $this->setDocument($document);
    }

    /**
     * Set document encoding
     *
     * @param  string $encoding
     * @return Zend_Dom_Query
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = (null === $encoding) ? null : (string) $encoding;
        return $this;
    }

    /**
     * Get document encoding
     *
     * @return null|string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set document to query
     *
     * @param  string|DOMDocument $document
     * @param  null|string $encoding Document encoding
     * @return Zend_Dom_Query
     */
    public function setDocument($document, $encoding = null)
    {
        if ($document instanceof DOMDocument) {
            return $this->setDocumentDom($document);
        }
        if (0 === strlen($document)) {
            return $this;
        }
        // breaking XML declaration to make syntax highlighting work
        if ('<' . '?xml' == substr(trim($document), 0, 5)) {
            if (preg_match('/<html[^>]*xmlns="([^"]+)"[^>]*>/i', $document, $matches)) {
                $this->_xpathNamespaces[] = $matches[1];
                return $this->setDocumentXhtml($document, $encoding);
            }
            return $this->setDocumentXml($document, $encoding);
        }
        if (strstr($document, 'DTD XHTML')) {
            return $this->setDocumentXhtml($document, $encoding);
        }
        return $this->setDocumentHtml($document, $encoding);
    }

    /**
     * Set DOMDocument to query
     *
     * @param  DOMDocument $document
     * @return Zend_Dom_Query
     */
    public function setDocumentDom(DOMDocument $document)
    {
        $this->_document = $document;
        $this->_docType  = self::DOC_DOM;
        if (null !== $document->encoding) {
            $this->setEncoding($document->encoding);
        }
        return $this;
    }

    /**
     * Register HTML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Zend_Dom_Query
     */
    public function setDocumentHtml($document, $encoding = null)
    {
        $this->_document = (string) $document;
        $this->_docType  = self::DOC_HTML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Register XHTML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Zend_Dom_Query
     */
    public function setDocumentXhtml($document, $encoding = null)
    {
        $this->_document = (string) $document;
        $this->_docType  = self::DOC_XHTML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Register XML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Zend_Dom_Query
     */
    public function setDocumentXml($document, $encoding = null)
    {
        $this->_document = (string) $document;
        $this->_docType  = self::DOC_XML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Retrieve current document
     *
     * @return string|DOMDocument
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Get document type
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->_docType;
    }

    /**
     * Get any DOMDocument errors found
     *
     * @return false|array
     */
    public function getDocumentErrors()
    {
        return $this->_documentErrors;
    }

    /**
     * Perform a CSS selector query
     *
     * @param  string $query
     * @return Zend_Dom_Query_Result
     */
    public function query($query)
    {
        $xpathQuery = Zend_Dom_Query_Css2Xpath::transform($query);
        return $this->queryXpath($xpathQuery, $query);
    }

    /**
     * Perform an XPath query
     *
     * @param  string|array $xpathQuery
     * @param  string       $query CSS selector query
     * @throws Zend_Dom_Exception
     * @return Zend_Dom_Query_Result
     */
    public function queryXpath($xpathQuery, $query = null)
    {
        if (null === ($document = $this->getDocument())) {
            #require_once 'Zend/Dom/Exception.php';
            throw new Zend_Dom_Exception('Cannot query; no document registered');
        }

        $encoding = $this->getEncoding();
        libxml_use_internal_errors(true);
        if (null === $encoding) {
            $domDoc = new DOMDocument('1.0');
        } else {
            $domDoc = new DOMDocument('1.0', $encoding);
        }
        $type   = $this->getDocumentType();
        switch ($type) {
            case self::DOC_DOM:
                $domDoc = $this->_document;
                $success = true;
                break;
            case self::DOC_XML:
                try {
                    $domDoc = Zend_Xml_Security::scan($document, $domDoc);
                    $success = ($domDoc !== false);
                } catch (Zend_Xml_Exception $e) {
                    #require_once 'Zend/Dom/Exception.php';
                    throw new Zend_Dom_Exception(
                        $e->getMessage()
                    );
                }
                break;
            case self::DOC_HTML:
            case self::DOC_XHTML:
            default:
                $success = $domDoc->loadHTML($document);
                break;
        }
        $errors = libxml_get_errors();
        if (!empty($errors)) {
            $this->_documentErrors = $errors;
            libxml_clear_errors();
        }
        libxml_use_internal_errors(false);

        if (!$success) {
            #require_once 'Zend/Dom/Exception.php';
            throw new Zend_Dom_Exception(sprintf('Error parsing document (type == %s)', $type));
        }

        $nodeList = $this->_getNodeList($domDoc, $xpathQuery);
        return new Zend_Dom_Query_Result($query, $xpathQuery, $domDoc, $nodeList);
    }

    /**
     * Register XPath namespaces
     *
     * @param array $xpathNamespaces
     */
    public function registerXpathNamespaces($xpathNamespaces)
    {
        $this->_xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Prepare node list
     *
     * @param  DOMDocument $document
     * @param  string|array $xpathQuery
     * @return array
     */
    protected function _getNodeList($document, $xpathQuery)
    {
        $xpath      = new DOMXPath($document);
        foreach ($this->_xpathNamespaces as $prefix => $namespaceUri) {
            $xpath->registerNamespace($prefix, $namespaceUri);
        }
        $xpathQuery = (string) $xpathQuery;
        if (preg_match_all('|\[contains\((@[a-z0-9_-]+),\s?\' |i', $xpathQuery, $matches)) {
            foreach ($matches[1] as $attribute) {
                $queryString = '//*[' . $attribute . ']';
                $attributeName = substr($attribute, 1);
                $nodes = $xpath->query($queryString);
                foreach ($nodes as $node) {
                    $attr = $node->attributes->getNamedItem($attributeName);
                    $attr->value = ' ' . $attr->value . ' ';
                }
            }
        }
        return $xpath->query($xpathQuery);
    }
}
