<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Form;

use Magento\Framework\Model\Exception;

/**
 * Eav Form Element Model
 *
 * @method \Magento\Eav\Model\Resource\Form\Element getResource()
 * @method int getTypeId()
 * @method \Magento\Eav\Model\Form\Element setTypeId(int $value)
 * @method int getFieldsetId()
 * @method \Magento\Eav\Model\Form\Element setFieldsetId(int $value)
 * @method int getAttributeId()
 * @method \Magento\Eav\Model\Form\Element setAttributeId(int $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Form\Element setSortOrder(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Form\Element');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Element
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Element\Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getTypeId()) {
            throw new Exception(__('Invalid form type.'));
        }
        if (!$this->getAttributeId()) {
            throw new Exception(__('Invalid EAV attribute'));
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
