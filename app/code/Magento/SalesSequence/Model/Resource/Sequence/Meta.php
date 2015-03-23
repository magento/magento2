<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Resource\Sequence;

use Magento\Framework\Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\AbstractModel as SalesAbstractModel;
use Magento\Framework\Model\Resource\Db\Context as DatabaseContext;
use Magento\SalesSequence\Model\Resource\Sequence\Profile as ResourceProfile;
use Magento\SalesSequence\Model\Sequence\MetaFactory;
use Magento\SalesSequence\Model\Sequence\Profile;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
/**
 * Class Meta
 */
class Meta extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_sequence_meta';

    /**
     * @var Profile
     */
    protected $resourceProfile;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    protected $ddlSequence;

    /**
     * @param DatabaseContext $context
     * @param MetaFactory $metaFactory
     * @param Profile $resourceProfile
     * @param null $resourcePrefix
     */
    public function __construct(
        DatabaseContext $context,
        MetaFactory $metaFactory,
        ResourceProfile $resourceProfile,
        DdlSequence $ddlSequence,
        $resourcePrefix = null
    ) {
        $this->ddlSequence = $ddlSequence;
        $this->metaFactory = $metaFactory;
        $this->resourceProfile = $resourceProfile;
        parent::__construct($context, $resourcePrefix);
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
     * @param string $entityType
     * @param int $storeId
     * @return \Magento\SalesSequence\Model\Sequence\Meta
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEntityTypeAndStore($entityType, $storeId)
    {
        $meta = $this->metaFactory->create();
        $adapter = $this->_getReadAdapter();
        $bind = ['entity_type' => $entityType, 'store_id' => $storeId];
        $select = $adapter->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'entity_type = :entity_type AND store_id = :store_id'
        );
        $metaId = $adapter->fetchOne($select, $bind);

        if ($metaId) {
            $this->load($meta, $metaId);
        }
        return $meta;
    }

    /**
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
        if (!$object->getData('active_profile') instanceof Profile) {
            throw new NoSuchEntityException(__('Entity Sequence profile not added to meta active profile'));
        }

        if (!$object->getData('entity_type') || !$object->getData('store_id') || !$object->getData('sequence_table')) {
            throw new Exception(__('Not enough arguments'));
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $profile = $object->getData('active_profile')
            ->setMetaId($object->getId());
        $this->resourceProfile->save($profile);
        return $this;
    }

    /**
     * Shortcut for sequence creation
     *
     * @param $sequenceName
     * @param $startNumber
     */
    public function createSequence($sequenceName, $startNumber)
    {
        $this->_getWriteAdapter()->query(
            $this->ddlSequence->getCreateSequenceDdl($sequenceName, $startNumber)
        );
    }
}
