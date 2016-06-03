<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\AttributeConfiguration;

use Magento\Eav\Setup\AttributeConfiguration\AdditionalConfigurationInterface;
use Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException;
use Magento\Eav\Setup\AttributeConfiguration\MainConfiguration;
use Magento\Framework\DataObject;

class CatalogConfiguration implements AdditionalConfigurationInterface
{
    /**
     * @var DataObject
     */
    private $attributeConfig;

    /**
     * @var MainConfiguration
     */
    private $mainConfiguration;

    /**
     * @param MainConfiguration $mainConfiguration
     */
    public function __construct(MainConfiguration $mainConfiguration)
    {
        $this->attributeConfig = new DataObject();
        $this->mainConfiguration = $mainConfiguration;
    }

    /**
     * @param string $type
     * @return CatalogConfiguration
     * @see \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function withFrontendInputRenderer($type)
    {
        return $this->getNewInstanceWithProperty('input_renderer', (string) $type, true);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function visible($flag = true)
    {
        return $this->getNewInstanceWithProperty('visible', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function searchable($flag = true)
    {
        return $this->getNewInstanceWithProperty('searchable', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function filterable($flag = true)
    {
        return $this->getNewInstanceWithProperty('filterable', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function comparable($flag = true)
    {
        return $this->getNewInstanceWithProperty('comparable', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function visibleOnFront($flag = true)
    {
        return $this->getNewInstanceWithProperty('visible_on_front', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function wysiwygEnabled($flag = true)
    {
        return $this->getNewInstanceWithProperty('wysiwyg_enabled', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function withHtmlAllowedOnFrontend($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_html_allowed_on_front', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function visibleInAdvancedSearch($flag = true)
    {
        return $this->getNewInstanceWithProperty('visible_in_advanced_search', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function filterableInSearch($flag = true)
    {
        return $this->getNewInstanceWithProperty('filterable_in_search', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function usedInProductListing($flag = true)
    {
        return $this->getNewInstanceWithProperty('used_in_product_listing', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function usedForSortBy($flag = true)
    {
        return $this->getNewInstanceWithProperty('used_for_sort_by', (bool) $flag);
    }

    /**
     * @param string[] $productTypes Examples: simple, virtual, bundle, downloadable, configurable
     * @return CatalogConfiguration
     */
    public function applyingTo(array $productTypes)
    {
        return $this->getNewInstanceWithProperty('apply_to', implode(',', $productTypes), true);
    }

    /**
     * @param int $position
     * @return CatalogConfiguration
     * @throws InvalidConfigurationException On non-integer position
     */
    public function withPosition($position)
    {
        if (!is_int($position)) {
            throw new InvalidConfigurationException(__('Non-integer catalog attribute position provided.'));
        }
        return $this->getNewInstanceWithProperty('position', $position);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function usedForPromoRules($flag = true)
    {
        return $this->getNewInstanceWithProperty('used_for_promo_rules', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function usedInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_used_in_grid', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function visibleInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_visible_in_grid', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CatalogConfiguration
     */
    public function filterableInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_filterable_in_grid', (bool) $flag);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->mainConfiguration->toArray(),
            $this->attributeConfig->toArray()
        );
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->attributeConfig = new DataObject($this->attributeConfig->toArray());
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param bool $withValueCheck
     * @return CatalogConfiguration
     * @throws InvalidConfigurationException
     */
    private function getNewInstanceWithProperty($propertyName, $propertyValue, $withValueCheck = false)
    {
        if ($withValueCheck && empty($propertyValue)) {
            throw new InvalidConfigurationException(__('Value of property "%1" is empty', $propertyName));
        }

        $newInstance = clone $this;
        $newInstance->attributeConfig->setData($propertyName, $propertyValue);
        return $newInstance;
    }
}
