<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel;

use Magento\Bundle\Model\Option\Validator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Bundle Option Resource Model
 */
class Option extends AbstractDb
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param Context $context
     * @param Validator $validator
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Validator $validator,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->validator = $validator;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
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
     * Remove selections by option id
     *
     * @param int $optionId
     *
     * @return int
     */
    public function removeOptionSelections($optionId)
    {
        return $this->getConnection()->delete(
            $this->getTable('catalog_product_bundle_selection'),
            ['option_id =?' => $optionId]
        );
    }

    /**
     * After save process
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);

        $connection = $this->getConnection();
        $data = new DataObject();
        $data->setOptionId($object->getId())
            ->setStoreId($object->getStoreId())
            ->setParentProductId($object->getParentId())
            ->setTitle($object->getTitle());

        $connection->insertOnDuplicate(
            $this->getTable('catalog_product_bundle_option_value'),
            $data->getData(),
            ['title']
        );

        /**
         * also saving default fallback value
         */
        if (0 !== (int)$object->getStoreId()) {
            $data->setStoreId(0)->setTitle($object->getDefaultTitle());
            $connection->insertOnDuplicate(
                $this->getTable('catalog_product_bundle_option_value'),
                $data->getData(),
                ['title']
            );
        }

        return $this;
    }

    /**
     * After delete process
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object)
    {
        parent::_afterDelete($object);

        $this->getConnection()
            ->delete(
                $this->getTable('catalog_product_bundle_option_value'),
                [
                    'option_id = ?' => $object->getId(),
                    'parent_product_id = ?' => $object->getParentId()
                ]
            );

        return $this;
    }

    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $connection = $this->getConnection();

        $title = $connection->getCheckSql(
            'option_title_store.title IS NOT NULL',
            'option_title_store.title',
            'option_title_default.title'
        );
        $bind = ['store_id' => $storeId, 'product_id' => $productId];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()
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
            ->join(
                ['e' => $this->getTable('catalog_product_entity')],
                "e.$linkField = opt.parent_id",
                []
            )
            ->where(
                'e.entity_id=:product_id'
            );
        if (!($searchData = $connection->fetchCol($select, $bind))) {
            $searchData = [];
        }

        return $searchData;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRulesBeforeSave()
    {
        return $this->validator;
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }
}
