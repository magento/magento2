<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\ResourceModel;

/**
 * Wee tax resource model
 *
 * @api
 * @since 100.0.2
 */
class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    private $weeeTaxCalculationsByEntityCache = [];

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
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
     */
    protected function _construct()
    {
        $this->_init('weee_tax', 'value_id');
    }

    /**
     * Fetch one calculated weee attribute from a select criteria
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    public function fetchOne($select)
    {
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Is there a weee attribute available for the location provided
     *
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @return boolean
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
     * Fetch calculated weee attributes by location, store and entity
     *
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @param int $storeId
     * @param int $entityId
     * @return array[]
     */
    public function fetchWeeeTaxCalculationsByEntity($countryId, $regionId, $websiteId, $storeId, $entityId)
    {
        $cacheKey = sprintf(
            '%s-%s-%s-%s-%s',
            $countryId,
            $regionId,
            $websiteId,
            $storeId,
            $entityId
        );
        if (!isset($this->weeeTaxCalculationsByEntityCache[$cacheKey])) {
                $attributeSelect = $this->getConnection()->select();
                $attributeSelect->from(
                    ['eavTable' => $this->getTable('eav_attribute')],
                    ['eavTable.attribute_code', 'eavTable.attribute_id', 'eavTable.frontend_label']
                )->joinLeft(
                    ['eavLabel' => $this->getTable('eav_attribute_label')],
                    'eavLabel.attribute_id = eavTable.attribute_id and eavLabel.store_id = ' . ((int)$storeId),
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
                $this->weeeTaxCalculationsByEntityCache[$cacheKey] = $values;
                return $values;
            }
        } else {
            return $this->weeeTaxCalculationsByEntityCache[$cacheKey];
        }

        return [];
    }
}
