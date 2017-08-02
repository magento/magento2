<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;

/**
 * Class \Magento\Framework\Indexer\Config\Converter
 *
 * @since 2.0.0
 */
class Converter implements ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \DOMXPath($source);
        $indexers = $xpath->evaluate('/config/indexer');
        /** @var $typeNode \DOMNode */
        foreach ($indexers as $indexerNode) {
            $data = [];
            $indexerId = $this->getAttributeValue($indexerNode, 'id');
            $data['indexer_id'] = $indexerId;
            $data['primary'] = $this->getAttributeValue($indexerNode, 'primary');
            $data['view_id'] = $this->getAttributeValue($indexerNode, 'view_id');
            $data['action_class'] = $this->getAttributeValue($indexerNode, 'class');
            $data['shared_index'] = $this->getAttributeValue($indexerNode, 'shared_index');
            $data['title'] = '';
            $data['description'] = '';

            /** @var $childNode \DOMNode */
            foreach ($indexerNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                /** @var $childNode \DOMElement */
                $data = $this->convertChild($childNode, $data);
            }
            if (!isset($data['dependencies'])) {
                $data['dependencies'] = [];
            }
            $output[$indexerId] = $data;
        }
        $output = $this->sortByDependencies($output);

        return $output;
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param mixed $default
     * @return null|string
     * @since 2.0.0
     */
    protected function getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }

    /**
     * Convert child from dom to array
     *
     * @param \DOMElement $childNode
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function convertChild(\DOMElement $childNode, $data)
    {
        $data['fieldsets'] = isset($data['fieldsets']) ? $data['fieldsets'] : [];
        switch ($childNode->nodeName) {
            case 'title':
                $data['title'] = $childNode->nodeValue;
                break;
            case 'description':
                $data['description'] = $childNode->nodeValue;
                break;
            case 'saveHandler':
                $data['saveHandler'] = $this->getAttributeValue($childNode, 'class');
                break;
            case 'structure':
                $data['structure'] = $this->getAttributeValue($childNode, 'class');
                break;
            case 'fieldset':
                $data = $this->convertFieldset($childNode, $data);
                break;
            case 'dependencies':
                $data = $this->convertDependencies($childNode, $data);
                break;
        }
        return $data;
    }

    /**
     * Convert fieldset
     *
     * @param \DOMElement $node
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function convertFieldset(\DOMElement $node, $data)
    {
        $data['fieldsets'] = isset($data['fieldsets']) ? $data['fieldsets'] : [];

        $data['fieldsets'][$this->getAttributeValue($node, 'name')] = [
            'source'   => $this->getAttributeValue($node, 'source'),
            'name'     => $this->getAttributeValue($node, 'name'),
            'provider' => $this->getAttributeValue($node, 'provider'),
            'fields'   => [],
        ];
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($childNode->nodeName) {
                case 'field':
                    $data['fieldsets'][$this->getAttributeValue($node, 'name')] = $this->convertField(
                        $childNode,
                        $data['fieldsets'][$this->getAttributeValue($node, 'name')]
                    );
                    break;
                case 'reference':
                    $data['fieldsets'][$this->getAttributeValue($node, 'name')]['references'] =
                        isset($data['fieldsets'][$this->getAttributeValue($node, 'name')]['references'])
                        ? $data['fieldsets'][$this->getAttributeValue($node, 'name')]['references']
                        : [];
                    $data['fieldsets'][$this->getAttributeValue($node, 'name')]['references']
                    [$this->getAttributeValue($childNode, 'fieldset')] =
                        isset(
                            $data['fieldsets'][$this->getAttributeValue($node, 'name')]
                            ['references'][$this->getAttributeValue($childNode, 'fieldset')]
                        )
                            ? $data['fieldsets'][$this->getAttributeValue($node, 'name')]
                        ['references'][$this->getAttributeValue($childNode, 'fieldset')]
                            : [];
                    $data['fieldsets'][$this->getAttributeValue($node, 'name')]['references']
                    [$this->getAttributeValue($childNode, 'fieldset')] = [
                        'fieldset' => $this->getAttributeValue($childNode, 'fieldset'),
                        'from'     => $this->getAttributeValue($childNode, 'from'),
                        'to'       => $this->getAttributeValue($childNode, 'to'),
                    ];
                    $this->addVirtualField(
                        $this->getAttributeValue($childNode, 'fieldset'),
                        $this->getAttributeValue($childNode, 'to'),
                        $data
                    );
                    $this->addVirtualField(
                        $this->getAttributeValue($node, 'name'),
                        $this->getAttributeValue($childNode, 'from'),
                        $data
                    );
                    break;
            }
        }
        return $this->sorting($data);
    }

    /**
     * Convert dependencies node
     *
     * @param \DOMElement $node
     * @param array $data
     * @return array
     */
    private function convertDependencies(\DOMElement $node, array $data)
    {
        $data['dependencies'] = $data['dependencies'] ?? [];

        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeName) {
                case 'indexer':
                    $indexerId = $this->getAttributeValue($childNode, 'id');
                    $data['dependencies'][] = $indexerId;
                    break;
            }
        }
        return $data;
    }

    /**
     * Add virtual field
     *
     * @param string $fieldset
     * @param string $field
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    protected function addVirtualField($fieldset, $field, $data)
    {
        if (!isset($data['fieldsets'][$fieldset]['fields'][$field])) {
            $data['fieldsets'][$fieldset]['fields'][$field] = [
                'type' => 'virtual',
                'name' => $field,
            ];
        }
    }

    /**
     * Convert field
     *
     * @param \DOMElement $node
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function convertField(\DOMElement $node, $data)
    {
        $data['fields'][$this->getAttributeValue($node, 'name')] = [
            'name'     => $this->getAttributeValue($node, 'name'),
            'handler'  => $this->getAttributeValue($node, 'handler'),
            'origin'   => $this->getAttributeValue($node, 'origin') ?: $this->getAttributeValue($node, 'name'),
            'dataType' => $this->getAttributeValue($node, 'dataType'),
            'type'     => $node->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'type'),
        ];

        $data['fields'][$this->getAttributeValue($node, 'name')]['filters'] = [];
        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $data['fields'][$this->getAttributeValue($node, 'name')]['filters'][]
                = $this->getAttributeValue($childNode, 'class');
        }
        return $data;
    }

    /**
     * Return node value translated if applicable
     *
     * @param \DOMNode $node
     * @return string
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected function getTranslatedNodeValue(\DOMNode $node)
    {
        $value = $node->nodeValue;
        if ($this->getAttributeValue($node, 'translate') == 'true') {
            $value = new \Magento\Framework\Phrase($value);
        }
        return $value;
    }

    /**
     * Sorting fieldset
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function sorting($data)
    {
        usort($data['fieldsets'], function ($current, $parent) use ($data) {
            if (!isset($current['references']) && $data['primary'] == $current['name']
                || isset($parent['references'][$current['name']])
            ) {
                return -1;
            } elseif (!isset($parent['references']) || isset($current['references'][$parent['name']])) {
                return 1;
            } else {
                return 0;
            }
        });
        return $data;
    }

    /**
     * Sort the list of indexers using "dependencies" node data.
     *
     * This method also sort data in the "dependencies" node of indexers.
     *
     * @param array $indexers
     * @return array
     */
    private function sortByDependencies($indexers)
    {
        $expanded = [];
        foreach (array_keys($indexers) as $indexerId) {
            $expanded[] = [
                'indexerId' => $indexerId,
                'dependencies' => $this->expandDependencies($indexers, $indexerId),
            ];
        }
        // Use "bubble sorting" because usort (which is using quicksort) is not a stable sort
        $total = count($expanded);
        for ($i = 0; $i < $total - 1; $i++) {
            for ($j = $i; $j < $total; $j++) {
                if (in_array($expanded[$j]['indexerId'], $expanded[$i]['dependencies'])) {
                    $temp = $expanded[$i];
                    $expanded[$i] = $expanded[$j];
                    $expanded[$j] = $temp;
                }
            }
        }

        $orderedIndexerIds = array_map(
            function ($item) {
                return $item['indexerId'];
            },
            $expanded
        );

        $result = [];
        foreach ($orderedIndexerIds as $indexerId) {
            $result[$indexerId] = $indexers[$indexerId];
            $result[$indexerId]['dependencies'] = array_values(
                array_intersect($orderedIndexerIds, $result[$indexerId]['dependencies'])
            );
        }

        return $result;
    }

    /**
     * Accumulate information about all transitive "dependencies" references.
     *
     * @param array $list
     * @param string $indexerId
     * @param array $accumulated
     * @return array
     * @throws ConfigurationMismatchException
     */
    private function expandDependencies($list, $indexerId, $accumulated = [])
    {
        $accumulated[] = $indexerId;
        $result = $list[$indexerId]['dependencies'];
        foreach ($result as $relatedIndexerId) {
            if (in_array($relatedIndexerId, $accumulated)) {
                throw new ConfigurationMismatchException(
                    new Phrase("Circular dependency references from '{$indexerId}' to '{$relatedIndexerId}'.")
                );
            }
            if (!isset($list[$relatedIndexerId])) {
                throw new ConfigurationMismatchException(
                    new Phrase(
                        "Dependency declaration '{$relatedIndexerId}' in "
                        . "'{$indexerId}' to the non-existing indexer."
                    )
                );
            }
            $relatedResult = $this->expandDependencies($list, $relatedIndexerId, $accumulated);
            $result = array_unique(array_merge($result, $relatedResult));
        }
        return $result;
    }
}
