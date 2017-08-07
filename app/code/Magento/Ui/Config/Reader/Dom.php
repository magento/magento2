<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Ui\Config\Converter;
use Magento\Framework\Config\Dom as ConfigDom;

/**
 * UI Component configuration file DOM object representation
 * @since 2.2.0
 */
class Dom extends ConfigDom
{
    /**
     * Id attribute list
     *
     * @var array
     * @since 2.2.0
     */
    private $idAttributes = [];

    /**
     * @var \DOMXPath
     * @since 2.2.0
     */
    private $domXPath;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     * @since 2.2.0
     */
    private $schemaFile;

    /**
     * @var SchemaLocatorInterface
     * @since 2.2.0
     */
    private $schemaLocator;

    /**
     * Dom constructor
     *
     * @param string $xml
     * @param ValidationStateInterface $validationState
     * @param SchemaLocatorInterface $schemaLocator
     * @param array $idAttributes
     * @param null $typeAttributeName
     * @param string $errorFormat
     * @since 2.2.0
     */
    public function __construct(
        $xml,
        ValidationStateInterface $validationState,
        SchemaLocatorInterface $schemaLocator,
        array $idAttributes = [],
        $typeAttributeName = null,
        $errorFormat = ConfigDom::ERROR_FORMAT_DEFAULT
    ) {
        $this->idAttributes = array_values($idAttributes);
        $this->schemaFile = $schemaLocator->getPerFileSchema() && $validationState->isValidationRequired()
            ? $schemaLocator->getPerFileSchema() : null;

        parent::__construct($xml, $validationState, $idAttributes, $typeAttributeName, $this->schemaFile, $errorFormat);
        $this->schemaLocator = $schemaLocator;
    }

    /**
     * Merge $xml into DOM document
     *
     * @param string $xml
     * @return void
     * @since 2.2.0
     */
    public function merge($xml)
    {
        $dom = $this->_initDom($xml);
        $this->domXPath = new \DOMXPath($this->getDom());
        $this->nestedMerge($this->getDom()->documentElement, $dom->childNodes);
    }

    /**
     * Merge nested xml nodes
     *
     * @param \DOMNode $contextNode
     * @param \DOMNodeList $insertedNodes
     * @return void
     * @since 2.2.0
     */
    private function nestedMerge(\DOMNode $contextNode, \DOMNodeList $insertedNodes)
    {
        foreach ($insertedNodes as $insertedItem) {
            switch ($insertedItem->nodeType) {
                case XML_COMMENT_NODE:
                    break;
                case XML_TEXT_NODE:
                case XML_CDATA_SECTION_NODE:
                    if (trim($insertedItem->textContent) !== '') {
                        $importNode = $this->getDom()->importNode($insertedItem, true);
                        $contextNode->insertBefore($importNode);
                    }
                    break;
                default:
                    $insertedXPath = $this->createXPath($insertedItem);
                    $rootMatchList = $this->domXPath->query($insertedXPath);

                    $jLength = $rootMatchList->length;
                    if ($jLength > 0) {
                        $this->processMatchedNodes($rootMatchList, $insertedItem);
                    } else {
                        $this->appendNode($insertedItem, $contextNode);
                    }

                    break;
            }
        }
    }

    /**
     * Merge node to matched root elements
     *
     * @param \DOMNodeList $rootMatchList
     * @param \DOMElement $insertedItem
     * @return void
     * @since 2.2.0
     */
    private function processMatchedNodes(\DOMNodeList $rootMatchList, \DOMElement $insertedItem)
    {
        foreach ($rootMatchList as $rootItem) {
            if ($this->_isTextNode($insertedItem) && $this->_isTextNode($rootItem)) {
                $rootItem->nodeValue = $insertedItem->nodeValue;
            } else {
                $this->nestedMerge($rootItem, $insertedItem->childNodes);
                $this->_mergeAttributes($rootItem, $insertedItem);
            }
        }
    }

    /**
     * Create XPath from node
     *
     * @param \DOMNode $node
     * @return string
     * @since 2.2.0
     */
    private function createXPath(\DOMNode $node)
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
                if (in_array($name, $this->idAttributes)) {
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
     * Append $insertedNode to $contextNode
     *
     * @param \DOMNode $insertedNode
     * @param \DOMNode $contextNode
     * @return void
     * @since 2.2.0
     */
    private function appendNode(\DOMNode $insertedNode, \DOMNode $contextNode)
    {
        $importNode = $this->getDom()->importNode($insertedNode, true);
        if (in_array($importNode->localName, [Converter::ARGUMENT_KEY, Converter::SETTINGS_KEY])) {
            $this->appendNodeToContext($contextNode, $importNode);
        } else {
            $contextNode->appendChild($importNode);
        }
    }

    /**
     * Append node to context node in correct position
     *
     * @param \DOMNode $contextNode
     * @param \DOMNode $importNode
     * @return void
     * @since 2.2.0
     */
    private function appendNodeToContext(\DOMNode $contextNode, \DOMNode $importNode)
    {
        if (!$contextNode->hasChildNodes()) {
            $contextNode->appendChild($importNode);
            return;
        }
        $childContextNode = null;
        /** @var \DOMNode $child */
        foreach ($contextNode->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($child->localName) {
                case Converter::ARGUMENT_KEY:
                    $childContextNode = $child->nextSibling;
                    break;
                case Converter::SETTINGS_KEY:
                    $childContextNode = $child;
                    break;
                default:
                    if (!$childContextNode) {
                        $childContextNode = $child;
                    }
                    break;
            }
        }

        $contextNode->insertBefore($importNode, $childContextNode);
    }
}
