<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Emtity attribute option model
 *
 * @method \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option _getResource()
 * @method \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option getResource()
 * @method int getAttributeId()
 * @method \Magento\Eav\Model\Entity\Attribute\Option setAttributeId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Option extends AbstractModel implements AttributeOptionInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Eav\Model\ResourceModel\Entity\Attribute\Option');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(AttributeOptionInterface::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(AttributeOptionInterface::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(AttributeOptionInterface::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData(AttributeOptionInterface::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->getData(AttributeOptionInterface::STORE_LABELS);
    }
    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(AttributeOptionInterface::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param string $value
     * @return string
     */
    public function setValue($value)
    {
        return $this->setData(AttributeOptionInterface::VALUE, $value);
    }

    /**
     * Set option order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(AttributeOptionInterface::SORT_ORDER, $sortOrder);
    }

    /**
     * set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(AttributeOptionInterface::IS_DEFAULT, $isDefault);
    }

    /**
     * Set option label for store scopes
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterface[] $storeLabels
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null)
    {
        return $this->setData(AttributeOptionInterface::STORE_LABELS, $storeLabels);
    }
}
