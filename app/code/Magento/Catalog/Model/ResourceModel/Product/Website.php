<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Catalog Product Website Resource Model
 */
class Website extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection and define resource table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_website', 'product_id');
    }

    /**
     * Removes products from websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Exception
     */
    public function removeProducts($websiteIds, $productIds)
    {
        if (!is_array($websiteIds) || !is_array($productIds) || count($websiteIds) == 0 || count($productIds) == 0) {
            return $this;
        }

        $connection = $this->getConnection();
        $whereCond = [
            $connection->quoteInto('website_id IN(?)', $websiteIds),
            $connection->quoteInto('product_id IN(?)', $productIds),
        ];
        $whereCond = join(' AND ', $whereCond);

        $connection->beginTransaction();
        try {
            $connection->delete($this->getMainTable(), $whereCond);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Add products to websites
     *
     * @param array $websiteIds
     * @param array $productIds
     * @return $this
     * @throws \Exception
     */
    public function addProducts($websiteIds, $productIds)
    {
        if (!is_array($websiteIds) || !is_array($productIds) || count($websiteIds) == 0 || count($productIds) == 0) {
            return $this;
        }

        $this->getConnection()->beginTransaction();

        // Before adding of products we should remove it old rows with same ids
        $this->removeProducts($websiteIds, $productIds);
        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($productIds as $productId) {
                    if (!$productId) {
                        continue;
                    }
                    $this->getConnection()->insert(
                        $this->getMainTable(),
                        ['product_id' => (int)$productId, 'website_id' => (int)$websiteId]
                    );
                }
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Retrieve product(s) website ids.
     *
     * @param array $productIds
     * @return array
     */
    public function getWebsites($productIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['product_id', 'website_id']
        )->where(
            'product_id IN (?)',
            $productIds
        );
        $rowset = $this->getConnection()->fetchAll($select);

        $result = [];
        foreach ($rowset as $row) {
            $result[$row['product_id']][] = $row['website_id'];
        }

        return $result;
    }
}
