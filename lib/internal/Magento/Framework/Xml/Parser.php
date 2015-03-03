<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

use \Magento\Framework\Config\Dom\ValidationException;

class Parser
{
    /**
     * Format of items in errors array to be used by default. Available placeholders - fields of \LibXMLError.
     */
    const ERROR_FORMAT_DEFAULT = "%message%\nLine: %line%\n";

    /**
     * @var \DOMDocument|null
     */
    protected $_dom = null;

    /**
     * @var \DOMDocument
     */
    protected $_currentDom;

    /**
     * @var array
     */
    protected $_content = [];

    /**
     * @var string
     */
    protected $_exceptionName = null;
    /**
     * Format of error messages
     *
     * @var string
     */
    protected $_errorFormat;

    /**
     * @param string|null $exceptionName
     * @param string $errorFormat
     */
    public function __construct($exceptionName = null, $errorFormat = self::ERROR_FORMAT_DEFAULT)
    {
        $this->_dom = new \DOMDocument();
        $this->_currentDom = $this->_dom;
        $this->_errorFormat = $errorFormat;
        $this->setExceptionName($exceptionName);
        return $this;
    }

    /**
     * @return \DOMDocument|null
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @return \DOMDocument
     */
    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    /**
     * @param \DOMDocument $node
     * @return $this
     */
    protected function _setCurrentDom($node)
    {
        $this->_currentDom = $node;
        return $this;
    }

    /**
     * @param string|null $exceptionName
     * @return $this
     */
    public function setExceptionName($exceptionName = null)
    {
        if ($exceptionName === null) {
            $exceptionName = '\Exception';
        }
        $this->_exceptionName = $exceptionName;
        return $this;
    }

    /**
     * @return array
     */
    public function xmlToArray()
    {
        $this->_content = $this->_xmlToArray();
        return $this->_content;
    }

    /**
     * @param bool $currentNode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _xmlToArray($currentNode = false)
    {
        if (!$currentNode) {
            $currentNode = $this->getDom();
        }
        $content = '';
        foreach ($currentNode->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $content = $content ?: [];

                    $value = null;
                    if ($node->hasChildNodes()) {
                        $value = $this->_xmlToArray($node);
                    }
                    $attributes = [];
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attribute) {
                            $attributes += [$attribute->name => $attribute->value];
                        }
                        $value = ['_value' => $value, '_attribute' => $attributes];
                    }
                    if (isset($content[$node->nodeName])) {
                        if (!isset($content[$node->nodeName][0]) || !is_array($content[$node->nodeName][0])) {
                            $oldValue = $content[$node->nodeName];
                            $content[$node->nodeName] = [];
                            $content[$node->nodeName][] = $oldValue;
                        }
                        $content[$node->nodeName][] = $value;
                    } else {
                        $content[$node->nodeName] = $value;
                    }
                    break;
                case XML_CDATA_SECTION_NODE:
                    $content = $node->nodeValue;
                    break;
                case XML_TEXT_NODE:
                    if (trim($node->nodeValue) !== '') {
                        $content = $node->nodeValue;
                    }
                    break;
            }
        }
        return $content;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function load($file)
    {
        libxml_use_internal_errors(true);
        $ok = $this->getDom()->load($file);
        $this->_validateLoad($ok);
        return $this;
    }

    /**
     * @param string $file
     * @param string $schemaFileName
     * @return bool
     * @throws \Exception
     */
    public function loadAndValidate($file, $schemaFileName)
    {
        $this->load($file);
        $e = self::validateDomDocument($this->getDom(), $schemaFileName, $this->_errorFormat, $this->_exceptionName);
        return empty($e);
    }

    /**
     * @param string $string
     * @return $this
     */
    public function loadXML($string)
    {
        $this->getDom()->loadXML($string);
        return $this;
    }
}
