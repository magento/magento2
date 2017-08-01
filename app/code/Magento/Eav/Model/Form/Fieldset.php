<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Form;

/**
 * Eav Form Fieldset Model
 *
 * @api
 * @method \Magento\Eav\Model\ResourceModel\Form\Fieldset getResource()
 * @method int getTypeId()
 * @method \Magento\Eav\Model\Form\Fieldset setTypeId(int $value)
 * @method string getCode()
 * @method \Magento\Eav\Model\Form\Fieldset setCode(string $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Form\Fieldset setSortOrder(int $value)
 * @since 2.0.0
 */
class Fieldset extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'eav_form_fieldset';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
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
        $this->_init(\Magento\Eav\Model\ResourceModel\Form\Fieldset::class);
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\ResourceModel\Form\Fieldset
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
     * @return \Magento\Eav\Model\ResourceModel\Form\Fieldset\Collection
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        if (!$this->getTypeId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form type.'));
        }
        if (!$this->getStoreId() && $this->getLabel()) {
            $this->setStoreLabel($this->getStoreId(), $this->getLabel());
        }

        return parent::beforeSave();
    }

    /**
     * Retrieve fieldset labels for stores
     *
     * @return array
     * @since 2.0.0
     */
    public function getLabels()
    {
        if (!$this->hasData('labels')) {
            $this->setData('labels', $this->_getResource()->getLabels($this));
        }
        return $this->_getData('labels');
    }

    /**
     * Set fieldset store labels
     * Input array where key - store_id and value = label
     *
     * @param array $labels
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setLabels(array $labels)
    {
        return $this->setData('labels', $labels);
    }

    /**
     * Set fieldset store label
     *
     * @param int $storeId
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setStoreLabel($storeId, $label)
    {
        $labels = $this->getLabels();
        $labels[$storeId] = $label;

        return $this->setLabels($labels);
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            $this->setData('store_id', $this->_storeManager->getStore()->getId());
        }
        return $this->_getData('store_id');
    }
}
