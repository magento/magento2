<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Module\Dir\Reader as DirectoryReader;
use \Magento\Framework\Config\ValidationStateInterface;

/**
 * Class DomMerger
 */
class DomMerger implements DomMergerInterface
{
    /**
     * Format of items in errors array to be used by default. Available placeholders - fields of \LibXMLError.
     */
    const ERROR_FORMAT_DEFAULT = "Message: %message%\nLine: %line%\n";

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    private $validationState;

    /**
     * Location schema file
     *
     * @var string
     */
    protected $schemaFilePath;

    /**
     * Result DOM document
     *
     * @var \DOMDocument
     */
    protected $domDocument;

    /**
     * Id attribute list
     *
     * @var array
     */
    protected $idAttributes = [];

    /**
     * Context XPath
     *
     * @var array
     */
    protected $contextXPath = [];

    /**
     * Is merge simple XML Element
     *
     * @var bool
     */
    protected $isMergeSimpleXMLElement;

    /**
     * Build DOM with initial XML contents and specifying identifier attributes for merging
     *
     * Format of $schema: Absolute schema file path or URN
     * Format of $idAttributes: array('name', 'id')
     * Format of $contextXPath: array('/config/ui')
     * The path to ID attribute name should not include any attribute notations or modifiers -- only node names
     *
     * @param ValidationStateInterface $validationState
     * @param string $schema
     * @param bool $isMergeSimpleXMLElement
     * @param array $contextXPath
     * @param array $idAttributes
     */
    public function __construct(
        ValidationStateInterface $validationState,
        $schema,
        $isMergeSimpleXMLElement = false,
        array $contextXPath = [],
        array $idAttributes = []
    ) {
        $this->validationState = $validationState;
        $this->schema = $schema;
        $this->isMergeSimpleXMLElement = $isMergeSimpleXMLElement;
        $this->contextXPath = $contextXPath;
        $this->idAttributes = $idAttributes;
    }

    /**
     * Is id attribute
     *
     * @param string $attributeName
     * @return bool
     */
    protected function isIdAttribute($attributeName)
    {
        return in_array($attributeName, $this->idAttributes);
    }

    /**
     * Is merge context
     *
     * @param string $xPath
     * @return bool
     */
    protected function isMergeContext($xPath)
    {
        foreach ($this->contextXPath as $context) {
            if (strpos($xPath, $context) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is context XPath
     *
     * @param array $xPath
     * @return bool
     */
    protected function isContextXPath(array $xPath)
    {
        return count(array_intersect($xPath, $this->contextXPath)) === count($xPath);
    }

    /**
     * Merges attributes of the merge node to the base node
     *
     * @param \DOMElement $baseNode
     * @param \DOMNode $mergeNode
     * @return void
     */
    protected function mergeAttributes(\DOMElement $baseNode, \DOMNode $mergeNode)
    {
        foreach ($mergeNode->attributes as $name => $attribute) {
            $baseNode->setAttribute($name, $attribute->value);
        }
    }

    /**
     * Create XPath
     *
     * @param \DOMNode $node
     * @return string
     */
    protected function createXPath(\DOMNode $node)
    {
        $parentXPath = '';
        $currentXPath = $node->getNodePath();
        if ($node->parentNode !== null && !$node->isSameNode($node->parentNode)) {
            $parentXPath = $this->createXPath($node->parentNode);
            $pathParts = explode('/', $currentXPath);
            $currentXPath = '/' . end($pathParts);
        }
        $attributesXPath = '';
        if ($node->hasAttributes()) {
            $attributes = [];
            foreach ($node->attributes as $name => $attribute) {
                if ($this->isIdAttribute($name)) {
                    $attributes[] = sprintf('@%s="%s"', $name, $attribute->value);
                    break;
                }
            }
            if (!empty($attributes)) {
                if (substr($currentXPath, -1) === ']') {
                    $currentXPath = substr($currentXPath, 0, strrpos($currentXPath, '['));
                }
                $attributesXPath = '[' . implode(' and ', $attributes) . ']';
            }
        }
        return '/' . trim($parentXPath . $currentXPath . $attributesXPath, '/');
    }

    /**
     * Merge nested xml nodes
     *
     * @param \DOMXPath $rootDomXPath
     * @param \DOMNodeList $insertedNodes
     * @param \DOMNode $contextNode
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function nestedMerge(\DOMXPath $rootDomXPath, \DOMNodeList $insertedNodes, \DOMNode $contextNode)
    {
        for ($i = 0, $iLength = $insertedNodes->length; $i < $iLength; ++$i) {
            $insertedItem = $insertedNodes->item($i);
            switch ($insertedItem->nodeType) {
                case XML_TEXT_NODE:
                case XML_COMMENT_NODE:
                case XML_CDATA_SECTION_NODE:
                    if (trim($insertedItem->textContent) !== '') {
                        $this->insertBefore($contextNode, $insertedItem);
                    }
                    break;
                default:
                    $insertedXPath = $this->createXPath($insertedItem);
                    $rootMatchList = $rootDomXPath->query($insertedXPath, $contextNode);
                    $jLength = $rootMatchList->length;
                    if ($jLength > 0) {
                        for ($j = 0; $j < $jLength; ++$j) {
                            $rootItem = $rootMatchList->item($j);
                            $rootItemXPath = $this->createXPath($rootItem);
                            if ($this->isMergeContext($insertedXPath)) {
                                if ($this->isTextNode($insertedItem) && $this->isTextNode($rootItem)) {
                                    $rootItem->nodeValue = $insertedItem->nodeValue;
                                } else if (!$this->isContextXPath([$rootItemXPath, $insertedXPath])
                                    && !$this->hasIdAttribute($rootItem)
                                    && !$this->hasIdAttribute($insertedItem)
                                ) {
                                    if ($this->isMergeSimpleXMLElement) {
                                        $this->nestedMerge($rootDomXPath, $insertedItem->childNodes, $rootItem);
                                        $this->mergeAttributes($rootItem, $insertedItem);
                                    } else {
                                        $this->appendChild($contextNode, $insertedItem);
                                    }
                                } else {
                                    $this->nestedMerge($rootDomXPath, $insertedItem->childNodes, $rootItem);
                                    $this->mergeAttributes($rootItem, $insertedItem);
                                }
                            } else {
                                $this->appendChild($contextNode, $insertedItem);
                            }
                        }
                    } else {
                        $this->appendChild($contextNode, $insertedItem);
                    }
                    break;
            }
        }
    }

    /**
     * Append child node
     *
     * @param \DOMNode $parentNode
     * @param \DOMNode $childNode
     * @return void
     */
    protected function appendChild(\DOMNode $parentNode, \DOMNode $childNode)
    {
        $importNode = $this->getDom()->importNode($childNode, true);
        $parentNode->appendChild($importNode);
    }

    /**
     * Insert before
     *
     * @param \DOMNode $parentNode
     * @param \DOMNode $childNode
     * @return void
     */
    protected function insertBefore(\DOMNode $parentNode, \DOMNode $childNode)
    {
        $importNode = $this->getDom()->importNode($childNode, true);
        $parentNode->insertBefore($importNode);
    }

    /**
     * Check if the node content is text
     *
     * @param \DOMNode $node
     * @return bool
     */
    protected function isTextNode(\DOMNode $node)
    {
        return $node->childNodes->length == 1 && $node->childNodes->item(0) instanceof \DOMText;
    }

    /**
     * Has ID attribute
     *
     * @param \DOMNode $node
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function hasIdAttribute(\DOMNode $node)
    {
        if (!$node->hasAttributes()) {
            return false;
        }

        foreach ($node->attributes as $name => $attribute) {
            if (in_array($name, $this->idAttributes)) {
                return true;
            }
        }

        return false;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function mergeNode(\DOMElement $node)
    {
        $parentDoom = $this->getDom();
        $this->nestedMerge(new \DOMXPath($parentDoom), $node->childNodes, $parentDoom->documentElement);
    }

    /**
     * Create DOM document based on $xml parameter
     *
     * @param string $xml
     * @return \DOMDocument
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createDomDocument($xml)
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($xml);
        if ($this->validationState->isValidationRequired() && $this->schema) {
            $errors = $this->validateDomDocument($domDocument);
            if (count($errors)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(implode("\n", $errors))
                );
            }
        }

        return $domDocument;
    }

    /**
     * Validate dom document
     *
     * @param \DOMDocument $domDocument
     * @param string|null $schemaFilePath
     * @return array of errors
     * @throws \Exception
     */
    protected function validateDomDocument(\DOMDocument $domDocument, $schema = null)
    {
        $schema = $schema !== null ? $schema : $this->schema;
        libxml_use_internal_errors(true);
        try {
            $errors = \Magento\Framework\Config\Dom::validateDomDocument($domDocument, $schema);
        } catch (\Exception $exception) {
            libxml_use_internal_errors(false);
            throw $exception;
        }
        libxml_use_internal_errors(false);

        return $errors;
    }

    /**
     * Render error message string by replacing placeholders '%field%' with properties of \LibXMLError
     *
     * @param \LibXMLError $errorInfo
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function renderErrorMessage(\LibXMLError $errorInfo)
    {
        $result = static::ERROR_FORMAT_DEFAULT;
        foreach ($errorInfo as $field => $value) {
            $result = str_replace('%' . $field . '%', trim((string)$value), $result);
        }
        if (strpos($result, '%') !== false) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'Error format "' . static::ERROR_FORMAT_DEFAULT . '" contains unsupported placeholders.'
                )
            );
        }

        return $result;
    }

    /**
     * Merge string $xml into DOM document
     *
     * @param string $xml
     * @return void
     */
    public function merge($xml)
    {
        if (!isset($this->domDocument)) {
            $this->domDocument = $this->createDomDocument($xml);
        } else {
            $this->mergeNode($this->createDomDocument($xml)->documentElement);
        }
    }

    /**
     * Get DOM document
     *
     * @return \DOMDocument
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDom()
    {
        if (!isset($this->domDocument)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Object DOMDocument should be created.')
            );
        }

        return $this->domDocument;
    }

    /**
     * Set DOM document
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    public function setDom(\DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
    }

    /**
     * Unset DOM document
     *
     * @return void
     */
    public function unsetDom()
    {
        unset($this->domDocument);
    }

    /**
     * Validate self contents towards to specified schema
     *
     * @param string|null $schemaFilePath
     * @return array
     */
    public function validate($schemaFilePath = null)
    {
        if (!$this->validationState->isValidationRequired()) {
            return [];
        }
        return $this->validateDomDocument($this->getDom(), $schemaFilePath);
    }
}
