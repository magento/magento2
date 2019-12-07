<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\ResourceModel\Rule\Collection;

/**
 * Abstract Rule entity resource collection model
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store associated with rule entities information map
     *
     * Example:
     * array(
     *    'entity_type1' => array(
     *        'associations_table' => 'table_name',
     *        'rule_id_field'      => 'rule_id',
     *        'entity_id_field'    => 'entity_id'
     *    ),
     *    'entity_type2' => array(
     *        'associations_table' => 'table_name',
     *        'rule_id_field'      => 'rule_id',
     *        'entity_id_field'    => 'entity_id'
     *    )
     *    ....
     * )
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * Add website ids to rules data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            /** @var \Magento\Rule\Model\AbstractModel $item */
            foreach ($this->_items as $item) {
                $item->afterLoad();
            }
        }

        return $this;
    }

    /**
     * Init flag for adding rule website ids to collection result
     *
     * @param bool|null $flag
     * @return $this
     */
    public function addWebsitesToResult($flag = null)
    {
        $flag = $flag === null ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    /**
     * Limit rules collection by specific websites
     *
     * @param int|int[]|\Magento\Store\Model\Website $websiteId
     * @return $this
     */
    public function addWebsiteFilter($websiteId)
    {
        if (!$this->getFlag('is_website_table_joined')) {
            $websiteIds = is_array($websiteId) ? $websiteId : [$websiteId];
            $entityInfo = $this->_getAssociatedEntityInfo('website');
            $this->setFlag('is_website_table_joined', true);
            foreach ($websiteIds as $index => $website) {
                if ($website instanceof \Magento\Store\Model\Website) {
                    $websiteIds[$index] = $website->getId();
                }
                $websiteIds[$index] = (int) $websiteIds[$index];
            }

            $websiteSelect = $this->getConnection()->select();
            $websiteSelect->from(
                $this->getTable($entityInfo['associations_table']),
                [$entityInfo['rule_id_field']]
            )->distinct(
                true
            )->where(
                $this->getConnection()->quoteInto($entityInfo['entity_id_field'] . ' IN (?)', $websiteIds)
            );
            $this->getSelect()->join(
                ['website' => $websiteSelect],
                'main_table.' . $entityInfo['rule_id_field'] . ' = website.' . $entityInfo['rule_id_field'],
                []
            );
        }
        return $this;
    }

    /**
     * Provide support for website id filter
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'website_ids') {
            return $this->addWebsiteFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }

    /**
     * Retrieve correspondent entity information of rule's associated entity by specified entity type
     *
     * (associations table name, columns names)
     *
     * @param string $entityType
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    protected function _getAssociatedEntityInfo($entityType)
    {
        if (isset($this->_associatedEntitiesMap[$entityType])) {
            return $this->_associatedEntitiesMap[$entityType];
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('There is no information about associated entity type "%1".', $entityType)
        );
    }
}
