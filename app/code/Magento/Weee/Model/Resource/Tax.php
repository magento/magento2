<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Resource;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Condition\ConditionInterface;

/**
 * Wee tax resource model
 */
class Tax extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $resourcePrefix = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $resourcePrefix);
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
     * Fetch one
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    public function fetchOne($select)
    {
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @return boolean
     */
    public function isWeeeInLocation($countryId, $regionId, $websiteId)
    {
        // Check if there is a weee_tax for the country and region
        $attributeSelect = $this->getReadConnection()->select();
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

        $value = $this->getReadConnection()->fetchOne($attributeSelect);
        if ($value) {
            return true;
        }

        return false;
    }
}
