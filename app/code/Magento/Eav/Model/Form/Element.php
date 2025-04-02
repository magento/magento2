<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Form;

use Magento\Framework\Exception\LocalizedException;

/**
 * Eav Form Element Model
 *
 * @api
 * @method int getTypeId()
 * @method \Magento\Eav\Model\Form\Element setTypeId(int $value)
 * @method int getFieldsetId()
 * @method \Magento\Eav\Model\Form\Element setFieldsetId(int $value)
 * @method int getAttributeId()
 * @method \Magento\Eav\Model\Form\Element setAttributeId(int $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Form\Element setSortOrder(int $value)
 * @since 100.0.2
 */
class Element extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_element';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\ResourceModel\Form\Element::class);
    }

    /**
     * Validate data before save data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getTypeId()) {
            throw new LocalizedException(__('The form type is invalid. Reset the type and try again.'));
        }
        if (!$this->getAttributeId()) {
            throw new LocalizedException(__('The EAV attribute is invalid. Verify the attribute and try again.'));
        }

        return parent::beforeSave();
    }

    /**
     * Retrieve EAV Attribute instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttribute()
    {
        if (!$this->hasData('attribute')) {
            $attribute = $this->_eavConfig->getAttribute($this->getEntityTypeId(), $this->getAttributeId());
            $this->setData('attribute', $attribute);
        }
        return $this->_getData('attribute');
    }
}
