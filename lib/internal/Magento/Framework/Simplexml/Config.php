<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Simplexml;

/**
 * Base class for simplexml based configurations
 *
 * @api
 * @since 100.0.2
 */
class Config
{
    /**
     * Configuration xml
     *
     * @var Element
     */
    protected $_xml = null;

    /**
     * Class name of simplexml elements for this configuration
     *
     * @var string
     */
    protected $_elementClass = \Magento\Framework\Simplexml\Element::class;

    /**
     * Xpath describing nodes in configuration that need to be extended
     *
     * @example <allResources extends="/config/modules//resource"/>
     */
    protected $_xpathExtends = "//*[@extends]";

    /**
     * Constructor
     *
     * Initializes XML for this configuration
     *
     * @see \Magento\Framework\Simplexml\Config::setXml
     * @param Element|string $sourceData
     */
    public function __construct($sourceData = null)
    {
        if ($sourceData === null) {
            return;
        }
        if ($sourceData instanceof Element) {
            $this->setXml($sourceData);
        } elseif (is_string($sourceData) && !empty($sourceData)) {
            if (strlen($sourceData) < 1000 && is_readable($sourceData)) {
                $this->loadFile($sourceData);
            } else {
                $this->loadString($sourceData);
            }
        }
    }

    /**
     * Sets xml for this configuration
     *
     * @param Element $node
     * @return $this
     */
    public function setXml(Element $node)
    {
        $this->_xml = $node;
        return $this;
    }

    /**
     * Returns node found by the $path
     *
     * @see \Magento\Framework\Simplexml\Element::descend
     * @param string $path
     * @return Element|bool
     */
    public function getNode($path = null)
    {
        if (!$this->getXml() instanceof Element) {
            return false;
        } elseif ($path === null) {
            return $this->getXml();
        } else {
            return $this->getXml()->descend($path);
        }
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return Element[]|bool
     */
    public function getXpath($xpath)
    {
        $xml = $this->getXml();
        if (empty($xml)) {
            return false;
        }

        if (!($result = @$xml->xpath($xpath))) {
            return false;
        }

        return $result;
    }

    /**
     * Return Xml of node as string
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->getNode()->asNiceXml('', false);
    }

    /**
     * Imports XML file
     *
     * @param string $filePath
     * @return boolean
     */
    public function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            //throw new \Exception('Can not read xml file '.$filePath);
            return false;
        }

        $fileData = file_get_contents($filePath);
        $fileData = $this->processFileData($fileData);
        return $this->loadString($fileData);
    }

    /**
     * Imports XML string
     *
     * @param string $string
     * @return boolean
     */
    public function loadString($string)
    {
        if (!empty($string)) {
            $xml = simplexml_load_string($string, $this->_elementClass);
            if ($xml) {
                $this->setXml($xml);
                return true;
            }
        }
        return false;
    }

    /**
     * Imports DOM node
     *
     * @param \DOMNode $dom
     * @return bool
     */
    public function loadDom(\DOMNode $dom)
    {
        $xml = simplexml_import_dom($dom, $this->_elementClass);
        if ($xml) {
            $this->setXml($xml);
            return true;
        }

        return false;
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param boolean $overwrite
     * @return $this
     */
    public function setNode($path, $value, $overwrite = true)
    {
        $this->getXml()->setNode($path, $value, $overwrite);
        return $this;
    }

    /**
     * Process configuration xml
     *
     * @return $this
     */
    public function applyExtends()
    {
        $targets = $this->getXpath($this->_xpathExtends);
        if (!$targets) {
            return $this;
        }
        foreach ($targets as $target) {
            $sources = $this->getXpath((string)$target['extends']);
            if ($sources) {
                foreach ($sources as $source) {
                    $target->extend($source);
                }
            }
        }
        return $this;
    }

    /**
     * Stub method for processing file data right after loading the file text
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        return $text;
    }

    /**
     * Enter description here...
     *
     * @param Config $config
     * @param boolean $overwrite
     * @return $this
     */
    public function extend(Config $config, $overwrite = true)
    {
        $this->getNode()->extend($config->getNode(), $overwrite);
        return $this;
    }

    /**
     * Cleanup circular references
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_xml = null;
    }

    /**
     * Getter for xml element
     *
     * @return Element
     */
    protected function getXml()
    {
        return $this->_xml;
    }
}
