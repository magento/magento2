<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        /** @var \DOMElement $child */
        foreach ($node->childNodes as $child) {
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
        /** @var \DOMElement $child */
        foreach ($node->childNodes as $child) {
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
            if ($this->hasNodeElement($child)) {
                $result = array_merge($result, $this->createRule($child));
            }
        }

        return [$node->getAttribute('target') => $result];
    }

    /**
     * Creating rules
     *
     * @param \DOMElement $node
     * @return array
     */
    protected function createRule(\DOMElement $node)
    {
        $result = [];
        $type = $node->getAttribute('type');
        /** @var \DOMElement $node */
        $result[$type] = [
            'event' => $node->getAttribute('event'),
        ];
        $result[$type]['argument'] = $this->createArgument($node);

        return $result;
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
        /** @var \DOMElement $child */
        foreach ($node->childNodes as $child) {
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
