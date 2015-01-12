<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Resource\Attribute;

/**
 * GoogleShopping Attributes collection
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Whether to join attribute_set_id to attributes or not
     *
     * @var bool
     */
    protected $_joinAttributeSetFlag = true;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Attribute', 'Magento\GoogleShopping\Model\Resource\Attribute');
    }

    /**
     * Add attribute set filter
     *
     * @param int $attributeSetId
     * @param string $targetCountry two words ISO format
     * @return $this
     */
    public function addAttributeSetFilter($attributeSetId, $targetCountry)
    {
        if (!$this->getJoinAttributeSetFlag()) {
            return $this;
        }
        $this->getSelect()->where('attribute_set_id = ?', $attributeSetId);
        $this->getSelect()->where('target_country = ?', $targetCountry);
        return $this;
    }

    /**
     * Add type filter
     *
     * @param int $type_id
     * @return $this
     */
    public function addTypeFilter($type_id)
    {
        $this->getSelect()->where('main_table.type_id = ?', $type_id);
        return $this;
    }

    /**
     * Load collection data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return  \Magento\GoogleShopping\Model\Resource\Attribute\Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        if ($this->getJoinAttributeSetFlag()) {
            $this->_joinAttributeSet();
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }

    /**
     * Join attribute sets data to select
     *
     * @return  \Magento\GoogleShopping\Model\Resource\Attribute\Collection
     */
    protected function _joinAttributeSet()
    {
        $this->getSelect()->joinInner(
            ['types' => $this->getTable('googleshopping_types')],
            'main_table.type_id=types.type_id',
            ['attribute_set_id' => 'types.attribute_set_id', 'target_country' => 'types.target_country']
        );
        return $this;
    }

    /**
     * Get flag - whether to join attribute_set_id to attributes or not
     *
     * @return bool
     */
    public function getJoinAttributeSetFlag()
    {
        return $this->_joinAttributeSetFlag;
    }

    /**
     * Set flag - whether to join attribute_set_id to attributes or not
     *
     * @param bool $flag
     * @return bool
     */
    public function setJoinAttributeSetFlag($flag)
    {
        return $this->_joinAttributeSetFlag = (bool)$flag;
    }
}
