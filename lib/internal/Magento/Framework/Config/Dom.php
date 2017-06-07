<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento configuration XML DOM utility
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Phrase;

/**
 * Class Dom
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Dom
{
    /**
     * Prefix which will be used for root namespace
     */
    const ROOT_NAMESPACE_PREFIX = 'x';

    /**
     * Format of items in errors array to be used by default. Available placeholders - fields of \LibXMLError.
     */
    const ERROR_FORMAT_DEFAULT = "%message%\nLine: %line%\n";

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    private $validationState;

    /**
     * Dom document
     *
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @var Dom\NodeMergingConfig
     */
    protected $nodeMergingConfig;

    /**
     * Name of attribute that specifies type of argument node
     *
     * @var string|null
     */
    protected $typeAttributeName;

    /**
     * Schema validation file
     *
     * @var string
     */
    protected $schema;

    /**
     * Format of error messages
     *
     * @var string
     */
    protected $errorFormat;

    /**
     * Default namespace for xml elements
     *
     * @var string
     */
    protected $rootNamespace;

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    private static $urnResolver;

    /**
     * @var array
     */
    private static $resolvedSchemaPaths = [];

    /**
     * Build DOM with initial XML contents and specifying identifier attributes for merging
     *
     * Format of $idAttributes: array('/xpath/to/some/node' => 'id_attribute_name')
     * The path to ID attribute name should not include any attribute notations or modifiers -- only node names
     *
     * @param string $xml
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param array $idAttributes
     * @param string $typeAttributeName
     * @param string $schemaFile
     * @param string $errorFormat
     */
    public function __construct(
        $xml,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        array $idAttributes = [],
        $typeAttributeName = null,
        $schemaFile = null,
        $errorFormat = self::ERROR_FORMAT_DEFAULT
    ) {
        $this->validationState = $validationState;
        $this->schema = $schemaFile;
        $this->nodeMergingConfig = new Dom\NodeMergingConfig(new Dom\NodePathMatcher(), $idAttributes);
        $this->typeAttributeName = $typeAttributeName;
        $this->errorFormat = $errorFormat;
        $this->dom = $this->_initDom($xml);
        $this->rootNamespace = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
    }

    /**
     * Retrieve array of xml errors
     *
     * @param string $errorFormat
     * @return string[]
     */
    private static function getXmlErrors($errorFormat)
    {
        $errors = [];
        $validationErrors = libxml_get_errors();
        if (count($validationErrors)) {
            foreach ($validationErrors as $error) {
                $errors[] = self::_renderErrorMessage($error, $errorFormat);
            }
        } else {
            $errors[] = 'Unknown validation error';
        }
        return $errors;
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
            if ($this->typeAttributeName &&
                $node->hasAttribute($this->typeAttributeName) &&
                $matchedNode->hasAttribute($this->typeAttributeName) &&
                $node->getAttribute($this->typeAttributeName) !== $matchedNode->getAttribute($this->typeAttributeName)
            ) {
                $parentMatchedNode = $this->_getMatchedNode($parentPath);
                $newNode = $this->dom->importNode($node, true);
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
        } else {
            /* Add node as is to the document under the same parent element */
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            $newNode = $this->dom->importNode($node, true);
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
        $prefix = $this->rootNamespace === null ? '' : self::ROOT_NAMESPACE_PREFIX . ':';
        $path = $parentPath . '/' . $prefix . $node->tagName;
        $idAttribute = $this->nodeMergingConfig->getIdAttribute($path);
        if (is_array($idAttribute)) {
            $constraints = [];
            foreach ($idAttribute as $attribute) {
                $value = $node->getAttribute($attribute);
                $constraints[] = "@{$attribute}='{$value}'";
            }
            $path .= '[' . implode(' and ', $constraints) . ']';
        } elseif ($idAttribute && ($value = $node->getAttribute($idAttribute))) {
            $path .= "[@{$idAttribute}='{$value}']";
        }
        return $path;
    }

    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @throws \Magento\Framework\Exception\LocalizedException An exception is possible if original document contains
     *     multiple nodes for identifier
     * @return \DOMElement|null
     */
    protected function _getMatchedNode($nodePath)
    {
        $xPath = new \DOMXPath($this->dom);
        if ($this->rootNamespace) {
            $xPath->registerNamespace(self::ROOT_NAMESPACE_PREFIX, $this->rootNamespace);
        }
        $matchedNodes = $xPath->query($nodePath);
        $node = null;
        if ($matchedNodes->length > 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase("More than one node matching the query: %1", [$nodePath])
            );
        } elseif ($matchedNodes->length == 1) {
            $node = $matchedNodes->item(0);
        }
        return $node;
    }

    /**
     * Validate dom document
     *
     * @param \DOMDocument $dom
     * @param string $schema Absolute schema file path or URN
     * @param string $errorFormat
     * @return array of errors
     * @throws \Exception
     */
    public static function validateDomDocument(
        \DOMDocument $dom,
        $schema,
        $errorFormat = self::ERROR_FORMAT_DEFAULT
    ) {
        if (!function_exists('libxml_set_external_entity_loader')) {
            return [];
        }

        if (!self::$urnResolver) {
            self::$urnResolver = new UrnResolver();
        }
        if (!isset(self::$resolvedSchemaPaths[$schema])) {
            self::$resolvedSchemaPaths[$schema] = self::$urnResolver->getRealPath($schema);
        }
        $schema = self::$resolvedSchemaPaths[$schema];

        libxml_use_internal_errors(true);
        libxml_set_external_entity_loader([self::$urnResolver, 'registerEntityLoader']);
        $errors = [];
        try {
            $result = $dom->schemaValidate($schema);
            if (!$result) {
                $errors = self::getXmlErrors($errorFormat);
            }
        } catch (\Exception $exception) {
            $errors = self::getXmlErrors($errorFormat);
            libxml_use_internal_errors(false);
            array_unshift($errors, new Phrase('Processed schema file: %1', [$schema]));
            throw new ValidationSchemaException(new Phrase(implode("\n", $errors)));
        }
        libxml_set_external_entity_loader(null);
        libxml_use_internal_errors(false);
        return $errors;
    }

    /**
     * Render error message string by replacing placeholders '%field%' with properties of \LibXMLError
     *
     * @param \LibXMLError $errorInfo
     * @param string $format
     * @return string
     * @throws \InvalidArgumentException
     */
    private static function _renderErrorMessage(\LibXMLError $errorInfo, $format)
    {
        $result = $format;
        foreach ($errorInfo as $field => $value) {
            $placeholder = '%' . $field . '%';
            $value = trim((string)$value);
            $result = str_replace($placeholder, $value, $result);
        }
        if (strpos($result, '%') !== false) {
            if (preg_match_all('/%.+%/', $result, $matches)) {
                $unsupported = [];
                foreach ($matches[0] as $placeholder) {
                    if (strpos($result, $placeholder) !== false) {
                        $unsupported[] = $placeholder;
                    }
                }
                if (!empty($unsupported)) {
                    throw new \InvalidArgumentException(
                        "Error format '{$format}' contains unsupported placeholders: " . implode(', ', $unsupported)
                    );
                }
            }
        }
        return $result;
    }

    /**
     * DOM document getter
     *
     * @return \DOMDocument
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * Create DOM document based on $xml parameter
     *
     * @param string $xml
     * @return \DOMDocument
     * @throws \Magento\Framework\Config\Dom\ValidationException
     */
    protected function _initDom($xml)
    {
        $dom = new \DOMDocument();
        $useErrors = libxml_use_internal_errors(true);
        $res = $dom->loadXML($xml);
        if (!$res) {
            $errors = self::getXmlErrors($this->errorFormat);
            libxml_use_internal_errors($useErrors);
            throw new \Magento\Framework\Config\Dom\ValidationException(implode("\n", $errors));
        }
        libxml_use_internal_errors($useErrors);
        if ($this->validationState->isValidationRequired() && $this->schema) {
            $errors = $this->validateDomDocument($dom, $this->schema, $this->errorFormat);
            if (count($errors)) {
                throw new \Magento\Framework\Config\Dom\ValidationException(implode("\n", $errors));
            }
        }
        return $dom;
    }

    /**
     * Validate self contents towards to specified schema
     *
     * @param string $schemaFileName absolute path to schema file
     * @param array &$errors
     * @return bool
     */
    public function validate($schemaFileName, &$errors = [])
    {
        if ($this->validationState->isValidationRequired()) {
            $errors = $this->validateDomDocument($this->dom, $schemaFileName, $this->errorFormat);
            return !count($errors);
        }
        return true;
    }

    /**
     * Set schema file
     *
     * @param string $schemaFile
     * @return $this
     */
    public function setSchemaFile($schemaFile)
    {
        $this->schema = $schemaFile;
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
        if ($attribute->prefix !== null && !empty($attribute->prefix)) {
            $attributeName = $attribute->prefix . ':' . $attribute->name;
        } else {
            $attributeName = $attribute->name;
        }
        return $attributeName;
    }
}
