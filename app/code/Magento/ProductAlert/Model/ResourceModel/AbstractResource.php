<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Product alert for back in abstract resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Retrieve alert row by object parameters
     *
     * @param AbstractModel $object
     * @return array|false
     */
    protected function _getAlertRow(AbstractModel $object)
    {
        $connection = $this->getConnection();
        if ($this->isExistAllBindIds($object)) {
            $select = $connection->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id = :customer_id'
            )->where(
                'product_id  = :product_id'
            )->where(
                'website_id  = :website_id'
            )->where(
                'store_id = :store_id'
            );
            $bind = [
                ':customer_id' => $object->getCustomerId(),
                ':product_id' => $object->getProductId(),
                ':website_id' => $object->getWebsiteId(),
                ':store_id' => $object->getStoreId()
            ];
            return $connection->fetchRow($select, $bind);
        }
        return false;
    }

    /**
     * Is exists all bind ids.
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isExistAllBindIds(AbstractModel $object): bool
    {
        return ($object->getCustomerId()
            && $object->getProductId()
            && $object->getWebsiteId()
            && $object->getStoreId());
    }

    /**
     * Load object data by parameters
     *
     * @param AbstractModel $object
     * @return $this
     */
    public function loadByParam(AbstractModel $object)
    {
        $row = $this->_getAlertRow($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }

    /**
     * Delete all customer alerts on website
     *
     * @param AbstractModel $object
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteCustomer(AbstractModel $object, $customerId, $websiteId = null)
    {
        $connection = $this->getConnection();
        $where = [];
        $where[] = $connection->quoteInto('customer_id=?', $customerId);
        if ($websiteId) {
            $where[] = $connection->quoteInto('website_id=?', $websiteId);
        }
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }
}
