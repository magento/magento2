<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Condition\ConditionInterface;

/**
 * Wee tax resource model
 *
 * @api
 * @since 2.0.0
 */
class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('weee_tax', 'value_id');
    }

    /**
     * Fetch one
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     * @since 2.0.0
     */
    public function fetchOne($select)
    {
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @return boolean
     * @since 2.0.0
     */
    public function isWeeeInLocation($countryId, $regionId, $websiteId)
    {
        // Check if there is a weee_tax for the country and region
        $attributeSelect = $this->getConnection()->select();
        $attributeSelect->from(
            $this->getTable('weee_tax'),
            'value'
        )->where(
            'website_id IN(?)',
            [$websiteId, 0]
        )->where(
            'country = ?',
            $countryId
        )->where(
            'state = ?',
            $regionId
        )->limit(
            1
        );

        $value = $this->getConnection()->fetchOne($attributeSelect);
        if ($value) {
            return true;
        }

        return false;
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @param int $storeId
     * @param int $entityId
     * @return array[]
     * @since 2.0.0
     */
    public function fetchWeeeTaxCalculationsByEntity($countryId, $regionId, $websiteId, $storeId, $entityId)
    {
        $attributeSelect = $this->getConnection()->select();
        $attributeSelect->from(
            ['eavTable' => $this->getTable('eav_attribute')],
            ['eavTable.attribute_code', 'eavTable.attribute_id', 'eavTable.frontend_label']
        )->joinLeft(
            ['eavLabel' => $this->getTable('eav_attribute_label')],
            'eavLabel.attribute_id = eavTable.attribute_id and eavLabel.store_id = ' .((int) $storeId),
            'eavLabel.value as label_value'
        )->joinInner(
            ['weeeTax' => $this->getTable('weee_tax')],
            'weeeTax.attribute_id = eavTable.attribute_id',
            'weeeTax.value as weee_value'
        )->where(
            'eavTable.frontend_input = ?',
            'weee'
        )->where(
            'weeeTax.website_id IN(?)',
            [$websiteId, 0]
        )->where(
            'weeeTax.country = ?',
            $countryId
        )->where(
            'weeeTax.state IN(?)',
            [$regionId, 0]
        )->where(
            'weeeTax.entity_id = ?',
            (int)$entityId
        );

        $order = ['weeeTax.state ' . \Magento\Framework\DB\Select::SQL_DESC,
            'weeeTax.website_id ' . \Magento\Framework\DB\Select::SQL_DESC];
        $attributeSelect->order($order);

        $values = $this->getConnection()->fetchAll($attributeSelect);

        if ($values) {
            return $values;
        }

        return [];
    }
}
