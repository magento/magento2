<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Entity/Attribute/Model - attribute selection source abstract
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class AbstractSource implements
    \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface,
    \Magento\Framework\Option\ArrayInterface
{
    /**
     * Reference to the attribute instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $_attribute;

    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     * @codeCoverageIgnore
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Get a text for option value
     *
     * @param  string|int $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        // Fixed for tax_class_id and custom_design
        if (count($options) > 0) {
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    return $option['label'] ?? $option['value'];
                }
            }
        }
        // End
        if (is_scalar($value) && isset($options[$value])) {
            return $options[$value];
        }
        return false;
    }

    /**
     * Get option id.
     *
     * @param string $value
     * @return null|string
     */
    public function getOptionId($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($this->mbStrcasecmp($option['label'], $value) == 0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function addValueSortToCollection($collection, $dir = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        return $this;
    }

    /**
     * Retrieve flat column definition
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function getFlatColumns()
    {
        return [];
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getFlatIndexes()
    {
        return [];
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function getFlatUpdateSelect($store)
    {
        return null;
    }

    /**
     * Get a text for index option value
     *
     * @param string|int $value
     * @return string|bool
     * @codeCoverageIgnore
     */
    public function getIndexOptionText($value)
    {
        return $this->getOptionText($value);
    }

    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Multibyte support strcasecmp function version.
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    private function mbStrcasecmp($str1, $str2)
    {
        $encoding = mb_internal_encoding();
        return strcmp(
            mb_strtoupper($str1, $encoding),
            mb_strtoupper($str2, $encoding)
        );
    }
}
