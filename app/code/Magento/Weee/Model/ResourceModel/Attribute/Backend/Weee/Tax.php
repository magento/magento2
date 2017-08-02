<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee;

/**
 * Catalog product WEEE tax backend attribute model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     * @since 2.0.0
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
     * Defines main resource table and table identifier field
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('weee_tax', 'value_id');
    }

    /**
     * Load product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     * @since 2.0.0
     */
    public function loadProductData($product, $attribute)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['website_id', 'country', 'state', 'value']
        )->where(
            'entity_id = ?',
            (int)$product->getId()
        )->where(
            'attribute_id = ?',
            (int)$attribute->getId()
        );
        if ($attribute->isScopeGlobal()) {
            $select->where('website_id = ?', 0);
        } else {
            $storeId = $product->getStoreId();
            if ($storeId) {
                $select->where(
                    'website_id IN (?)',
                    [0, $this->_storeManager->getStore($storeId)->getWebsiteId()]
                );
            }
        }
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Delete product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return $this
     * @since 2.0.0
     */
    public function deleteProductData($product, $attribute)
    {
        $where = ['entity_id = ?' => (int)$product->getId(), 'attribute_id = ?' => (int)$attribute->getId()];

        $connection = $this->getConnection();
        if (!$attribute->isScopeGlobal()) {
            $storeId = $product->getStoreId();
            if ($storeId) {
                $where['website_id IN(?)'] = [0, $this->_storeManager->getStore($storeId)->getWebsiteId()];
            }
        }
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }

    /**
     * Insert product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function insertProductData($product, $data)
    {
        $data['entity_id'] = (int)$product->getId();
        $this->getConnection()->insert($this->getMainTable(), $data);
        return $this;
    }
}
