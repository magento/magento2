<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Form;

/**
 * Eav Form Type Model
 *
 * @method \Magento\Eav\Model\ResourceModel\Form\Type getResource()
 * @method string getCode()
 * @method \Magento\Eav\Model\Form\Type setCode(string $value)
 * @method string getLabel()
 * @method \Magento\Eav\Model\Form\Type setLabel(string $value)
 * @method int getIsSystem()
 * @method \Magento\Eav\Model\Form\Type setIsSystem(int $value)
 * @method string getTheme()
 * @method \Magento\Eav\Model\Form\Type setTheme(string $value)
 * @method int getStoreId()
 * @method \Magento\Eav\Model\Form\Type setStoreId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'eav_form_type';

    /**
     * @var \Magento\Eav\Model\Form\FieldsetFactory
     * @since 2.0.0
     */
    protected $_fieldsetFactory;

    /**
     * @var \Magento\Eav\Model\Form\ElementFactory
     * @since 2.0.0
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Form\FieldsetFactory $fieldsetFactory
     * @param \Magento\Eav\Model\Form\ElementFactory $elementFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Form\FieldsetFactory $fieldsetFactory,
        \Magento\Eav\Model\Form\ElementFactory $elementFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_elementFactory = $elementFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\ResourceModel\Form\Type::class);
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\ResourceModel\Form\Type
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return \Magento\Eav\Model\ResourceModel\Form\Type\Collection
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Retrieve assigned Eav Entity types
     *
     * @return array
     * @since 2.0.0
     */
    public function getEntityTypes()
    {
        if (!$this->hasData('entity_types')) {
            $this->setData('entity_types', $this->_getResource()->getEntityTypes($this));
        }
        return $this->_getData('entity_types');
    }

    /**
     * Set assigned Eav Entity types
     *
     * @param array $entityTypes
     * @return $this
     * @since 2.0.0
     */
    public function setEntityTypes(array $entityTypes)
    {
        $this->setData('entity_types', $entityTypes);
        return $this;
    }

    /**
     * Assign Entity Type to Form Type
     *
     * @param int $entityTypeId
     * @return $this
     * @since 2.0.0
     */
    public function addEntityType($entityTypeId)
    {
        $entityTypes = $this->getEntityTypes();
        if (!empty($entityTypeId) && !in_array($entityTypeId, $entityTypes)) {
            $entityTypes[] = $entityTypeId;
            $this->setEntityTypes($entityTypes);
        }
        return $this;
    }

    /**
     * Copy Form Type properties from skeleton form type
     *
     * @param \Magento\Eav\Model\Form\Type $skeleton
     * @return $this
     * @since 2.0.0
     */
    public function createFromSkeleton(\Magento\Eav\Model\Form\Type $skeleton)
    {
        $fieldsetCollection = $this->_fieldsetFactory->create()->getCollection()->addTypeFilter(
            $skeleton
        )->setSortOrder();
        $elementCollection = $this->_elementFactory->create()->getCollection()->addTypeFilter(
            $skeleton
        )->setSortOrder();

        // copy fieldsets
        $fieldsetMap = [];
        foreach ($fieldsetCollection as $skeletonFieldset) {
            $this->_fieldsetFactory->create()->setTypeId(
                $this->getId()
            )->setCode(
                $skeletonFieldset->getCode()
            )->setLabels(
                $skeletonFieldset->getLabels()
            )->setSortOrder(
                $skeletonFieldset->getSortOrder()
            )->save();
            $fieldsetMap[$skeletonFieldset->getId()] = $this->_fieldsetFactory->create()->getId();
        }

        // copy elements
        foreach ($elementCollection as $skeletonElement) {
            $fieldsetId = null;
            if ($skeletonElement->getFieldsetId()) {
                $fieldsetId = $fieldsetMap[$skeletonElement->getFieldsetId()];
            }
            $this->_elementFactory->create()->setTypeId(
                $this->getId()
            )->setFieldsetId(
                $fieldsetId
            )->setAttributeId(
                $skeletonElement->getAttributeId()
            )->setSortOrder(
                $skeletonElement->getSortOrder()
            );
        }

        return $this;
    }
}
