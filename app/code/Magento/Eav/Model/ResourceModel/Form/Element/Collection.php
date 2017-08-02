<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Form\Element;

/**
 * Eav Form Element Resource Collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\Form\Element::class, \Magento\Eav\Model\ResourceModel\Form\Element::class);
    }

    /**
     * Add Form Type filter to collection
     *
     * @param \Magento\Eav\Model\Form\Type|int $type
     * @return $this
     * @since 2.0.0
     */
    public function addTypeFilter($type)
    {
        if ($type instanceof \Magento\Eav\Model\Form\Type) {
            $type = $type->getId();
        }

        return $this->addFieldToFilter('type_id', $type);
    }

    /**
     * Add Form Fieldset filter to collection
     *
     * @param \Magento\Eav\Model\Form\Fieldset|int $fieldset
     * @return $this
     * @since 2.0.0
     */
    public function addFieldsetFilter($fieldset)
    {
        if ($fieldset instanceof \Magento\Eav\Model\Form\Fieldset) {
            $fieldset = $fieldset->getId();
        }

        return $this->addFieldToFilter('fieldset_id', $fieldset);
    }

    /**
     * Add Attribute filter to collection
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|int $attribute
     *
     * @return $this
     * @since 2.0.0
     */
    public function addAttributeFilter($attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            $attribute = $attribute->getId();
        }

        return $this->addFieldToFilter('attribute_id', $attribute);
    }

    /**
     * Set order by element sort order
     *
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Join attribute data
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _joinAttributeData()
    {
        $this->getSelect()->join(
            ['eav_attribute' => $this->getTable('eav_attribute')],
            'main_table.attribute_id = eav_attribute.attribute_id',
            ['attribute_code', 'entity_type_id']
        );

        return $this;
    }

    /**
     * Load data (join attribute data)
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @since 2.0.0
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $this->_joinAttributeData();
        }
        return parent::load($printQuery, $logQuery);
    }
}
