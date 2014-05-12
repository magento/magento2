<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
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
     * Fetch column
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return array
     */
    public function fetchCol($select)
    {
        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Update discount percents
     *
     * @return \Magento\Weee\Model\Resource\Tax
     */
    public function updateDiscountPercents()
    {
        return $this->_updateDiscountPercents();
    }

    /**
     * Update products discount persent
     *
     * @param Product|ConditionInterface|int $condition
     * @return $this
     */
    public function updateProductsDiscountPercent($condition)
    {
        return $this->_updateDiscountPercents($condition);
    }

    /**
     * Update tax percents for WEEE based on products condition
     *
     * @param Product|ConditionInterface|int $productCondition
     * @return $this
     */
    protected function _updateDiscountPercents($productCondition = null)
    {
        $now = $this->dateTime->toTimestamp($this->dateTime->now());
        $adapter = $this->_getWriteAdapter();

        $select = $this->_getReadAdapter()->select();
        $select->from(array('data' => $this->getTable('catalogrule_product')));

        $deleteCondition = '';
        if ($productCondition) {
            if ($productCondition instanceof Product) {
                $select->where('product_id = ?', (int)$productCondition->getId());
                $deleteCondition = $adapter->quoteInto('entity_id=?', (int)$productCondition->getId());
            } elseif ($productCondition instanceof ConditionInterface) {
                $productCondition = $productCondition->getIdsSelect($adapter)->__toString();
                $select->where("product_id IN ({$productCondition})");
                $deleteCondition = "entity_id IN ({$productCondition})";
            } else {
                $select->where('product_id = ?', (int)$productCondition);
                $deleteCondition = $adapter->quoteInto('entity_id = ?', (int)$productCondition);
            }
        } else {
            $select->where('(from_time <= ? OR from_time = 0)', $now)->where('(to_time >= ? OR to_time = 0)', $now);
        }
        $adapter->delete($this->getTable('weee_discount'), $deleteCondition);

        $select->order(array('data.website_id', 'data.customer_group_id', 'data.product_id', 'data.sort_order'));

        $data = $this->_getReadAdapter()->query($select);

        $productData = array();
        $stops = array();
        $prevKey = false;
        while ($row = $data->fetch()) {
            $key = "{$row['product_id']}-{$row['website_id']}-{$row['customer_group_id']}";
            if (isset($stops[$key]) && $stops[$key]) {
                continue;
            }

            if ($prevKey && $prevKey != $key) {
                foreach ($productData as $product) {
                    $adapter->insert($this->getTable('weee_discount'), $product);
                }
                $productData = array();
            }
            if ($row['action_operator'] == 'by_percent') {
                if (isset($productData[$key])) {
                    $productData[$key]['value'] -= $productData[$key]['value'] / 100 * $row['action_amount'];
                } else {
                    $productData[$key] = array(
                        'entity_id' => $row['product_id'],
                        'customer_group_id' => $row['customer_group_id'],
                        'website_id' => $row['website_id'],
                        'value' => 100 - max(0, min(100, $row['action_amount']))
                    );
                }
            }

            if ($row['action_stop']) {
                $stops[$key] = true;
            }
            $prevKey = $key;
        }
        foreach ($productData as $product) {
            $adapter->insert($this->getTable('weee_discount'), $product);
        }

        return $this;
    }

    /**
     * Retrieve product discount percent
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $customerGroupId
     * @return string
     */
    public function getProductDiscountPercent($productId, $websiteId, $customerGroupId)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getTable('weee_discount'),
            'value'
        )->where(
            'website_id = ?',
            (int)$websiteId
        )->where(
            'entity_id = ?',
            (int)$productId
        )->where(
            'customer_group_id = ?',
            (int)$customerGroupId
        );

        return $this->_getReadAdapter()->fetchOne($select);
    }
}
