<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Magento configuration XML DOM utility
 */
namespace Magento\Framework\Config;

use \Magento\Framework\Xml\Parser;

/**
 * Class Dom
 */
class Dom
{
    /**
     * Prefix which will be used for root namespace
     */
    const ROOT_NAMESPACE_PREFIX = 'x';

    /**
     * Dom document
     *
     * @var \DOMDocument
     */
    protected $_dom;

    /**
     * @var Dom\NodeMergingConfig
     */
    protected $_nodeMergingConfig;

    /**
     * Name of attribute that specifies type of argument node
     *
     * @var string|null
     */
    protected $_typeAttributeName;

    /**
     * Schema validation file
     *
     * @var string
     */
    protected $_schemaFile;

    /**
     * Format of error messages
     *
     * @var string
     */
    protected $_errorFormat;

    /**
     * Default namespace for xml elements
     *
     * @var string
     */
    protected $_rootNamespace;

    /**
     * Build DOM with initial XML contents and specifying identifier attributes for merging
     *
     * Format of $idAttributes: array('/xpath/to/some/node' => 'id_attribute_name')
     * The path to ID attribute name should not include any attribute notations or modifiers -- only node names
     *
     * @param string $xml
     * @param array $idAttributes
     * @param string $typeAttributeName
     * @param string $schemaFile
     * @param string $errorFormat
     */
    public function __construct(
        $xml,
        array $idAttributes = [],
        $typeAttributeName = null,
        $schemaFile = null,
	$errorFormat = Parser::ERROR_FORMAT_DEFAULT
    ) {
        $this->_schemaFile = $schemaFile;
        $this->_nodeMergingConfig = new Dom\NodeMergingConfig(new Dom\NodePathMatcher(), $idAttributes);
        $this->_typeAttributeName = $typeAttributeName;
        $this->_errorFormat = $errorFormat;
        $this->_dom = $this->_initDom($xml);
	if ($this->_dom) {
	    $this->_rootNamespace = $this->_dom->lookupNamespaceUri($this->_dom->namespaceURI);
	}
    }

    /**
     * Merge $xml into DOM document
     *
     * @param string $xml
     * @return void
     */
    public function merge($xml)
    {
        $dom = $this->_initDom($xml);
        $this->_mergeNode($dom->documentElement, '');
    }

    /**
     * Recursive merging of the \DOMElement into the original document
     *
     * Algorithm:
     * 1. Find the same node in original document
     * 2. Extend and override original document node attributes and scalar value if found
     * 3. Append new node if original document doesn't have the same node
     *
     * @param \DOMElement $node
     * @param string $parentPath path to parent node
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mergeNode(\DOMElement $node, $parentPath)
    {
        $path = $this->_getNodePathByParent($node, $parentPath);

        $matchedNode = $this->_getMatchedNode($path);

        /* Update matched node attributes and value */
        if ($matchedNode) {
            //different node type
            if ($this->_typeAttributeName && $node->hasAttribute(
                $this->_typeAttributeName
            ) && $matchedNode->hasAttribute(
                $this->_typeAttributeName
            ) && $node->getAttribute(
                $this->_typeAttributeName
            ) !== $matchedNode->getAttribute(
                $this->_typeAttributeName
            )
            ) {
                $parentMatchedNode = $this->_getMatchedNode($parentPath);
                $newNode = $this->_dom->importNode($node, true);
                $parentMatchedNode->replaceChild($newNode, $matchedNode);
                return;
            }

            $this->_mergeAttributes($matchedNode, $node);
            if (!$node->hasChildNodes()) {
                return;
            }
            /* override node value */
            if ($this->_isTextNode($node)) {
                /* skip the case when the matched node has children, otherwise they get overridden */
                if (!$matchedNode->hasChildNodes() || $this->_isTextNode($matchedNode)) {
                    $matchedNode->nodeValue = $node->childNodes->item(0)->nodeValue;
                }
            } else {
                /* recursive merge for all child nodes */
                foreach ($node->childNodes as $childNode) {
                    if ($childNode instanceof \DOMElement) {
                        $this->_mergeNode($childNode, $path);
                    }
                }
            }
	} elseif ($this->_dom) {
            /* Add node as is to the document under the same parent element */
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            $newNode = $this->_dom->importNode($node, true);
            $parentMatchedNode->appendChild($newNode);
        }
    }

    /**
     * Check if the node content is text
     *
     * @param \DOMElement $node
     * @return bool
     */
    protected function _isTextNode($node)
    {
        return $node->childNodes->length == 1 && $node->childNodes->item(0) instanceof \DOMText;
    }

    /**
     * Merges attributes of the merge node to the base node
     *
     * @param \DOMElement $baseNode
     * @param \DOMNode $mergeNode
     * @return void
     */
    protected function _mergeAttributes($baseNode, $mergeNode)
    {
        foreach ($mergeNode->attributes as $attribute) {
            $baseNode->setAttribute($this->_getAttributeName($attribute), $attribute->value);
        }
    }

    /**
     * Identify node path based on parent path and node attributes
     *
     * @param \DOMElement $node
     * @param string $parentPath
     * @return string
     */
    protected function _getNodePathByParent(\DOMElement $node, $parentPath)
    {
        $prefix = is_null($this->_rootNamespace) ? '' : self::ROOT_NAMESPACE_PREFIX . ':';
        $path = $parentPath . '/' . $prefix . $node->tagName;
        $idAttribute = $this->_nodeMergingConfig->getIdAttribute($path);
        if (is_array($idAttribute)) {
            $constraints = [];
            foreach ($idAttribute as $attribute) {
                $value = $node->getAttribute($attribute);
                $constraints[] = "@{$attribute}='{$value}'";
            }
            $path .= '[' . join(' and ', $constraints) . ']';
        } elseif ($idAttribute && ($value = $node->getAttribute($idAttribute))) {
            $path .= "[@{$idAttribute}='{$value}']";
        }
        return $path;
    }

    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @throws \Magento\Framework\Exception An exception is possible if original document contains multiple nodes for identifier
     * @return \DOMElement|null
     */
    protected function _getMatchedNode($nodePath)
    {
	if (!$this->_dom) {
	    return null;
	}
        $xPath = new \DOMXPath($this->_dom);
        if ($this->_rootNamespace) {
            $xPath->registerNamespace(self::ROOT_NAMESPACE_PREFIX, $this->_rootNamespace);
        }
        $matchedNodes = $xPath->query($nodePath);
        $node = null;
        if ($matchedNodes->length > 1) {
            throw new \Magento\Framework\Exception("More than one node matching the query: {$nodePath}");
        } elseif ($matchedNodes->length == 1) {
            $node = $matchedNodes->item(0);
        }
        return $node;
    }

    /**
     * DOM document getter
     *
     * @return \DOMDocument|null
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * Create DOM document based on $xml parameter
     *
     * @param string $xml
     * @return \DOMDocument|null
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _initDom($xml)
    {
	$parser = new Parser('\Magento\Framework\Config\Dom\ValidationException', $this->_errorFormat);
        if ($this->_schemaFile) {
	    return $parser->loadXMLandValidate($xml, $this->_schemaFile) === true
		? $parser->getDom()
		: null;
        }
	return $parser->loadXML($xml)->getDom();
    }

    /**
     * Validate self contents towards to specified schema
     *
     * @param string $schemaFileName absolute path to schema file
     * @param array &$errors
     * @param string $exceptionName
     * @return bool
     */
    public function validate($schemaFileName, &$errors = [], $exceptionName = '\Exception')
    {
	$errors = Parser::validateDomDocument($this->_dom, $schemaFileName, $this->_errorFormat, $exceptionName);
        return !count($errors);
    }

    /**
     * Set schema file
     *
     * @param string $schemaFile
     * @return $this
     */
    public function setSchemaFile($schemaFile)
    {
        $this->_schemaFile = $schemaFile;
        return $this;
    }

    /**
     * Returns the attribute name with prefix, if there is one
     *
     * @param \DOMAttr $attribute
     * @return string
     */
    private function _getAttributeName($attribute)
    {
        if (!is_null($attribute->prefix) && !empty($attribute->prefix)) {
            $attributeName = $attribute->prefix . ':' . $attribute->name;
        } else {
            $attributeName = $attribute->name;
        }
        return $attributeName;
    }
}
