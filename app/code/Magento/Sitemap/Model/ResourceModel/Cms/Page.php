<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Sitemap cms page collection model
 *
 * @api
 * @since 2.0.0
 */
class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var EntityManager
     * @since 2.1.0
     */
    protected $entityManager;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param string $connectionName
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model (catalog/category)
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('cms_page', 'page_id');
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(PageInterface::class)->getEntityConnection();
    }

    /**
     * Retrieve cms page collection array
     *
     * @param int $storeId
     * @return array
     * @since 2.0.0
     */
    public function getCollection($storeId)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier != ?',
            \Magento\Cms\Model\Page::NOROUTE_PAGE_ID
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $pages = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }

    /**
     * Prepare page object
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function _prepareObject(array $data)
    {
        $object = new \Magento\Framework\DataObject();
        $object->setId($data[$this->getIdFieldName()]);
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);

        return $object;
    }

    /**
     * Load an object
     *
     * @param CmsPage|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @since 2.1.0
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $isId = true;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $value = count($result) ? $result[0] : $value;
            $isId = count($result);
        }

        if ($isId) {
            $this->entityManager->load($object, $value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function save(AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->beginTransaction();

        try {
            if (!$this->isModified($object)) {
                $this->processNotModifiedSave($object);
                $this->commit();
                $object->setHasDataChanges(false);
                return $this;
            }
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                $this->_serializeFields($object);
                $this->_beforeSave($object);
                $this->_checkUnique($object);
                $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());
                $this->entityManager->save($object);
                $this->unserializeFields($object);
                $this->processAfterSaves($object);
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
