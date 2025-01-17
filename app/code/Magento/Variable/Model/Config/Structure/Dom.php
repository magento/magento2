<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Variable\Model\Config\Structure;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Filter dom structure to required components only
 */
class Dom extends \Magento\Framework\Config\Dom
{
    /**
     * @var AvailableVariables
     */
    private $structureConfig;

    /**
     * @var array
     */
    private $filters;

    /**
     * @param string $xml
     * @param ValidationStateInterface $validationState
     * @param array $idAttributes
     * @param string $typeAttributeName
     * @param string $schemaFile
     * @param string $errorFormat
     * @param AvailableVariables|null $availableVariables
     */
    public function __construct(
        $xml,
        ValidationStateInterface $validationState,
        array $idAttributes = [],
        $typeAttributeName = null,
        $schemaFile = null,
        $errorFormat = self::ERROR_FORMAT_DEFAULT,
        ?AvailableVariables $availableVariables = null
    ) {
        $this->structureConfig = $availableVariables
            ?: ObjectManager::getInstance()->get(AvailableVariables::class);
        parent::__construct($xml, $validationState, $idAttributes, $typeAttributeName, $schemaFile, $errorFormat);
    }

    /**
     * @inheritdoc
     */
    protected function _initDom($xml)
    {
        $dom = parent::_initDom($xml);
        foreach (['tab', 'section', 'group', 'field'] as $element) {
            $this->filterElements($dom, $element, $this->getElementFilters($element));
        }
        return $dom;
    }

    /**
     * @inheritdoc
     */
    public function merge($xml)
    {
        $dom = $this->_initDom($xml);
        if ($dom->documentElement->getElementsByTagName('section')->length >0) {
            $this->_mergeNode($dom->documentElement, '');
        }
    }

    /**
     * Filter DOMDocument elements and keep only allowed
     *
     * @param \DOMDocument $dom
     * @param string $tag
     * @param array $ids
     */
    private function filterElements($dom, $tag, $ids)
    {
        $removeElements = [];
        foreach ($dom->documentElement->getElementsByTagName($tag) as $removeElement) {
            if (!in_array($removeElement->getAttribute('id'), $ids)) {
                $removeElements[] = $removeElement;
            }
        }
        foreach ($removeElements as $removeElement) {
            $removeElement->parentNode->removeChild($removeElement);
        }
    }

    /**
     * Get allowed identifiers by element tag
     *
     * @param string $tag
     * @return array|mixed
     */
    private function getElementFilters($tag)
    {
        if (!isset($this->filters[$tag])) {
            $configPaths = $this->structureConfig->getFlatConfigPaths();
            $filterData = [];
            foreach (array_keys($configPaths) as $path) {
                list($section, $group, $field) = explode('/', $path);
                $filterData['section'][] = $section;
                $filterData['group'][] = $group;
                $filterData['field'][] = $field;
            }
            $filterData['tab'] = [];
            $this->filters = array_map('array_unique', $filterData);
        }

        return $this->filters[$tag] ?? [];
    }
}
