<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

class Parser
{
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
     * @var boolean
     */
    protected $errorHandlerIsActive = false;

    /**
     *
     */
    public function __construct()
    {
        $this->_dom = new \DOMDocument();
        $this->_currentDom = $this->_dom;
        return $this;
    }

    /**
     * Initializes error handler
     *
     * @return void
     */
    public function initErrorHandler()
    {
        $this->errorHandlerIsActive = true;
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
        $this->getDom()->load($file);
        return $this;
    }

    /**
     * @param string $string
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadXML($string)
    {
        if ($this->errorHandlerIsActive) {
            set_error_handler([$this, 'errorHandler']);
        }

        try {
            $this->getDom()->loadXML($string);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            restore_error_handler();
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase($e->getMessage()),
                $e
            );
        }

        if ($this->errorHandlerIsActive) {
            restore_error_handler();
        }

        return $this;
    }

    /**
     * Custom XML lib error handler
     *
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param int $errorLine
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function errorHandler($errorNo, $errorStr, $errorFile, $errorLine)
    {
        if ($errorNo != 0) {
            $message = "{$errorStr} in {$errorFile} on line {$errorLine}";
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($message));
        }
    }
}
