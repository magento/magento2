<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Rules;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Converter
 */
class Converter implements ConverterInterface
{
    /**
     * Convert dom document
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $result = [];
        if ($source->documentElement->hasChildNodes()) {
            /** @var \DOMElement $child */
            foreach ($source->documentElement->childNodes as $child) {
                if ($this->hasNodeElement($child)) {
                    $id = $child->getAttribute('id');
                    $result[$id] = [
                        'events' => [],
                        'relations' => []
                    ];
                    /** @var \DOMElement $paymentChild */
                    foreach ($child->childNodes as $paymentChild) {
                        switch ($paymentChild->nodeName) {
                            case 'events':
                                $selector = $paymentChild->getAttribute('selector');
                                $result[$id]['events'][$selector] = $this->createEvents($paymentChild);
                                break;
                            case 'relation':
                                $result[$id]['relations'] += $this->createRelation($paymentChild);
                                break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Creating events
     *
     * @param \DOMElement $node
     * @return array
     */
    protected function createEvents(\DOMElement $node)
    {
        $result = [];
        foreach ($node->childNodes as $child) {
            /** @var \DOMElement $child */
            if ($this->hasNodeElement($child)) {
                $result[$child->getAttribute('name')] = [
                    'value' => $child->getAttribute('value'),
                    'predicate' => $this->createPredicate($child),
                ];
            }
        }

        return $result;
    }

    /**
     * Creating configuration for function predicate
     *
     * @param \DOMElement $node
     * @return array
     */
    protected function createPredicate(\DOMElement $node)
    {
        $result = [];
        foreach ($node->childNodes as $child) {
            /** @var \DOMElement $child */
            if ($this->hasNodeElement($child)) {
                $result = [
                    'name' => $child->getAttribute('name'),
                    'message' => __($child->getAttribute('message')),
                    'event' => $child->getAttribute('event'),
                    'argument' => $this->createArgument($child),
                ];
            }
        }

        return $result;
    }

    /**
     * Creating relationships
     *
     * @param \DOMElement $node
     * @return array
     */
    protected function createRelation(\DOMElement $node)
    {
        $result = [];
        foreach ($node->childNodes as $child) {
            /** @var \DOMElement $child */
            if ($this->hasNodeElement($child)) {
                $result[$child->getAttribute('type')][] = [
                    'event' => $child->getAttribute('event'),
                    'argument' => $this->createArgument($child),
                ];
            }
        }

        return [$node->getAttribute('target') => $result];
    }

    /**
     * Create argument
     *
     * @param \DOMElement $node
     * @return array
     */
    protected function createArgument(\DOMElement $node)
    {
        $result = [];
        foreach ($node->childNodes as $child) {
            /** @var \DOMElement $child */
            if ($this->hasNodeElement($child)) {
                $result[$child->getAttribute('name')] = $child->textContent;
            }
        }

        return $result;
    }

    /**
     * Check whether the node has DOMElement
     *
     * @param \DOMNode $node
     * @return bool
     */
    protected function hasNodeElement(\DOMNode $node)
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
            case XML_COMMENT_NODE:
            case XML_CDATA_SECTION_NODE:
                return false;
        }

        return true;
    }
}
