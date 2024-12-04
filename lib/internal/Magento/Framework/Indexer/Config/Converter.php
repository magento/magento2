<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;
use Magento\Framework\Indexer\Config\Converter\SortingAdjustmentInterface;

class Converter implements ConverterInterface
{
    /**
     * @var SortingAdjustmentInterface
     */
    private SortingAdjustmentInterface $sortingAdjustment;

    /**
     * @param SortingAdjustmentInterface $sortingAdjustment
     */
    public function __construct(SortingAdjustmentInterface $sortingAdjustment)
    {
        $this->sortingAdjustment = $sortingAdjustment;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
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
            $data['dependencies'] = [];

            /** @var $childNode \DOMNode */
            foreach ($indexerNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                /** @var $childNode \DOMElement */
                $data = $this->convertChild($childNode, $data);
            }
            $output[$indexerId] = $data;
        }
        $output = $this->sortByDependencies($output);
        return $this->sortingAdjustment->adjust($output);
    }

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param mixed $default
     * @return null|string
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
    private function convertDependencies(\DOMElement $node, array $data): array
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
     * @deprecated 101.0.0
     * @see not used anymore
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
    private function sortByDependencies(array $indexers): array
    {
        $expanded = [];
        foreach (array_keys($indexers) as $indexerId) {
            $expanded[] = [
                'indexerId' => $indexerId,
                'dependencies' => $this->expandDependencies($indexers, $indexerId),
            ];
        }
        /**
         * Used this algorithm of sorting not quicksort, because it guarantees
         * correct sequence of indexers with multiple dependencies.
         */
        $maxIndex = count($expanded) - 1;
        for ($i = 0; $i < $maxIndex; $i++) {
            for ($j = $i + 1; $j <= $maxIndex; $j++) {
                if (in_array($expanded[$j]['indexerId'], $expanded[$i]['dependencies'], true)) {
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
                array_intersect($orderedIndexerIds, $result[$indexerId]['dependencies'] ?? [])
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
    private function expandDependencies(array $list, string $indexerId, array $accumulated = []): array
    {
        $accumulated[] = $indexerId;
        $result = $list[$indexerId]['dependencies'] ?? [];
        $addedResult = [];
        foreach ($result as $relatedIndexerId) {
            if (in_array($relatedIndexerId, $accumulated)) {
                throw new ConfigurationMismatchException(
                    new Phrase(
                        "Circular dependency references from '%indexerId' to '%relatedIndexerId'.",
                        [
                            'indexerId' => $indexerId,
                            'relatedIndexerId' => $relatedIndexerId,
                        ]
                    )
                );
            }
            if (!isset($list[$relatedIndexerId])) {
                throw new ConfigurationMismatchException(
                    new Phrase(
                        "Dependency declaration '%relatedIndexerId' in "
                        . "'%indexerId' to the non-existing indexer.",
                        [
                            'indexerId' => $indexerId,
                            'relatedIndexerId' => $relatedIndexerId,
                        ]
                    )
                );
            }
            $relatedResult = $this->expandDependencies($list, $relatedIndexerId, $accumulated);
            $addedResult[] = $relatedResult;
        }
        $result = array_merge($result, ...$addedResult);
        return array_unique($result);
    }
}
