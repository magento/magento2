<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog product WEEE tax backend attribute model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Defines main resource table and table identifier field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('weee_tax', 'value_id');
    }

    /**
     * Load product data
     *
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return array
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
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return $this
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
     * @param Product $product
     * @param array $data
     * @return $this
     */
    public function insertProductData($product, $data)
    {
        $data['entity_id'] = (int)$product->getId();
        $this->getConnection()->insert($this->getMainTable(), $data);
        return $this;
    }
}
