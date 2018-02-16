<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException as Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Context as DatabaseContext;
use Magento\SalesSequence\Model\ResourceModel\Profile as ResourceProfile;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\Profile as ModelProfile;

/**
 * Class Meta represents metadata for sequence as sequence table and store id
 */
class Meta extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_sequence_meta';

    /**
     * @var ResourceProfile
     */
    protected $resourceProfile;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @param DatabaseContext $context
     * @param MetaFactory $metaFactory
     * @param ResourceProfile $resourceProfile
     * @param string $connectionName
     */
    public function __construct(
        DatabaseContext $context,
        MetaFactory $metaFactory,
        ResourceProfile $resourceProfile,
        $connectionName = null
    ) {
        $this->metaFactory = $metaFactory;
        $this->resourceProfile = $resourceProfile;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_sequence_meta', 'meta_id');
    }

    /**
     * Retrieves Metadata for entity by entity type and store id
     *
     * @param string $entityType
     * @param int $storeId
     * @return \Magento\SalesSequence\Model\Meta
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEntityTypeAndStore($entityType, $storeId)
    {
        $meta = $this->metaFactory->create();
        $connection = $this->getConnection();
        $bind = ['entity_type' => $entityType, 'store_id' => $storeId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'entity_type = :entity_type AND store_id = :store_id'
        );
        $metaId = $connection->fetchOne($select, $bind);

        if ($metaId) {
            $this->load($meta, $metaId);
        }
        return $meta;
    }

    /**
     * Using for load sequence profile and setting it into metadata
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setData(
            'active_profile',
            $this->resourceProfile->loadActiveProfile($object->getId())
        );
        return $this;
    }

    /**
     * Validate metadata and sequence before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws Exception
     * @throws NoSuchEntityException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getData('active_profile') instanceof ModelProfile) {
            throw new NoSuchEntityException(__('Entity Sequence profile not added to meta active profile'));
        }

        if (!$object->getData('entity_type')
            || $object->getData('store_id') === null
            || !$object->getData('sequence_table')
        ) {
            throw new Exception(__('Not enough arguments'));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $profile = $object->getData('active_profile')
            ->setMetaId($object->getId());
        $this->resourceProfile->save($profile);
        return $this;
    }
}
