<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Resource;

/**
 * Bundle Option Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Bundle\Model\Option\Validator
     */
    private $validator;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Bundle\Model\Option\Validator $validator
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Bundle\Model\Option\Validator $validator
    ) {
        parent::__construct($resource);
        $this->validator = $validator;
    }

    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_option', 'option_id');
    }

    /**
     * After save process
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        $condition = [
            'option_id = ?' => $object->getId(),
            'store_id = ? OR store_id = 0' => $object->getStoreId()
        ];

        $write = $this->_getWriteAdapter();
        $write->delete($this->getTable('catalog_product_bundle_option_value'), $condition);

        $data = new \Magento\Framework\Object();
        $data->setOptionId($object->getId())
            ->setStoreId($object->getStoreId())
            ->setTitle($object->getTitle());

        $write->insert($this->getTable('catalog_product_bundle_option_value'), $data->getData());

        /**
         * also saving default value if this store view scope
         */

        if ($object->getStoreId()) {
            $data->setStoreId(0);
            $data->setTitle($object->getDefaultTitle());
            $write->insert($this->getTable('catalog_product_bundle_option_value'), $data->getData());
        }

        return $this;
    }

    /**
     * After delete process
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);

        $this->_getWriteAdapter()
            ->delete(
                $this->getTable('catalog_product_bundle_option_value'),
                ['option_id = ?' => $object->getId()]
            );

        return $this;
    }

    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $adapter = $this->_getReadAdapter();

        $title = $adapter->getCheckSql(
            'option_title_store.title IS NOT NULL',
            'option_title_store.title',
            'option_title_default.title'
        );
        $bind = ['store_id' => $storeId, 'product_id' => $productId];
        $select = $adapter->select()
            ->from(
                ['opt' => $this->getMainTable()],
                []
            )
            ->join(
                ['option_title_default' => $this->getTable('catalog_product_bundle_option_value')],
                'option_title_default.option_id = opt.option_id AND option_title_default.store_id = 0',
                []
            )
            ->joinLeft(
                ['option_title_store' => $this->getTable('catalog_product_bundle_option_value')],
                'option_title_store.option_id = opt.option_id AND option_title_store.store_id = :store_id',
                ['title' => $title]
            )
            ->where(
                'opt.parent_id=:product_id'
            );
        if (!($searchData = $adapter->fetchCol($select, $bind))) {
            $searchData = [];
        }

        return $searchData;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
