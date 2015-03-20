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
        $resourcePrefix = null
    )
    {
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
    public function loadBy($entityType, $storeId)
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
        $object->setData('active_profile', $this->resourceProfile->loadActiveProfile($object));
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
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
        $object->getData('active_profile')
            ->setMetaId($object->getId())
            ->setIsActive(1)
            ->save();
        return $this;
    }
}
