<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

/**
 * Class \Magento\Framework\Xml\Parser
 *
 * @since 2.0.0
 */
class Parser
{
    /**
     * @var \DOMDocument|null
     * @since 2.0.0
     */
    protected $_dom = null;

    /**
     * @var \DOMDocument
     * @since 2.0.0
     */
    protected $_currentDom;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_content = [];

    /**
     * @var boolean
     * @since 2.0.0
     */
    protected $errorHandlerIsActive = false;

    /**
     *
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function initErrorHandler()
    {
        $this->errorHandlerIsActive = true;
    }

    /**
     * @return \DOMDocument|null
     * @since 2.0.0
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @return \DOMDocument
     * @since 2.0.0
     */
    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    /**
     * @param \DOMDocument $node
     * @return $this
     * @since 2.0.0
     */
    protected function _setCurrentDom($node)
    {
        $this->_currentDom = $node;
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
                        if ((is_string($content[$node->nodeName]) || !isset($content[$node->nodeName][0]))
                            || (is_array($value) && !is_array($content[$node->nodeName][0]))
                        ) {
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function errorHandler($errorNo, $errorStr, $errorFile, $errorLine)
    {
        if ($errorNo != 0) {
            $message = "{$errorStr} in {$errorFile} on line {$errorLine}";
            throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase($message));
        }
    }
}
