<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\AttributeConfiguration;

use Magento\Eav\Setup\AttributeConfiguration\AdditionalConfigurationInterface;
use Magento\Eav\Setup\AttributeConfiguration\MainConfiguration;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class CustomerConfiguration implements AdditionalConfigurationInterface
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
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function system($flag = true)
    {
        return $this->getNewInstanceWithProperty('system', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function visible($flag = true)
    {
        return $this->getNewInstanceWithProperty('visible', (bool) $flag);
    }

    /**
     * @param string $type
     * @return CustomerConfiguration
     * @see \Magento\Framework\Data\Form\Filter\FilterInterface
     * @see \Magento\Framework\Data\Form\FilterFactory
     */
    public function withInputFilter($type)
    {
        return $this->getNewInstanceWithProperty('input_filter', (string) $type, true);
    }

    /**
     * @param int $count Must be a positive integer
     * @return CustomerConfiguration
     * @throws LocalizedException
     */
    public function withMultiLineCount($count)
    {
        if (is_int($count) && $count >= 0) {
            return $this->getNewInstanceWithProperty('multiline_count', $count);
        } else {
            throw new LocalizedException(__('Non-integer or negative multi-line count provided.'));
        }
    }

    /**
     * @param string $type
     * @return CustomerConfiguration
     * @see \Magento\Eav\Model\Attribute\Data\AbstractData
     */
    public function withDataModel($type)
    {
        return $this->getNewInstanceWithProperty('data', (string) $type, true);
    }

    /**
     * @param int $sortOrder
     * @return CustomerConfiguration
     * @throws LocalizedException
     */
    public function withSortOrder($sortOrder)
    {
        if (!is_int($sortOrder)) {
            throw new LocalizedException(__('Non-integer attribute sort order provided.'));
        }
        return $this->getNewInstanceWithProperty('position', $sortOrder);
    }

    /**
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function usedInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_used_in_grid', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function visibleInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_visible_in_grid', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function filterableInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_filterable_in_grid', (bool) $flag);
    }

    /**
     * @param bool $flag
     * @return CustomerConfiguration
     */
    public function searchableInGrid($flag = true)
    {
        return $this->getNewInstanceWithProperty('is_searchable_in_grid', (bool) $flag);
    }

    /**
     * @param array $rules
     * @return CustomerConfiguration
     */
    public function withValidationRules(array $rules)
    {
        return $this->getNewInstanceWithProperty('validate_rules', $rules, true);
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
        $this->mainConfiguration = clone $this->mainConfiguration;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param bool $withValueCheck
     * @return CustomerConfiguration
     * @throws LocalizedException
     */
    private function getNewInstanceWithProperty($propertyName, $propertyValue, $withValueCheck = false)
    {
        if ($withValueCheck && empty($propertyValue)) {
            throw new LocalizedException(__('Value of property "%1" is empty', $propertyName));
        }

        $newInstance = clone $this;
        $newInstance->attributeConfig->setData($propertyName, $propertyValue);
        return $newInstance;
    }
}
