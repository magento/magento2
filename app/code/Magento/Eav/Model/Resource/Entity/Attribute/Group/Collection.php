<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute\Group;

/**
 * Eav attribute group resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Init resource model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Entity\Attribute\Group', 'Magento\Eav\Model\Resource\Entity\Attribute\Group');
    }

    /**
     * Set Attribute Set Filter
     *
     * @param int $setId
     * @return $this
     */
    public function setAttributeSetFilter($setId)
    {
        $this->addFieldToFilter('attribute_set_id', ['eq' => $setId]);
        $this->setOrder('sort_order');
        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $direction
     * @return $this
     */
    public function setSortOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->addOrder('sort_order', $direction);
    }
}
