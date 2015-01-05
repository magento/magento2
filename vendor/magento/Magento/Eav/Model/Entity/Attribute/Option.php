<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Emtity attribute option model
 *
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Option _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Option getResource()
 * @method int getAttributeId()
 * @method \Magento\Eav\Model\Entity\Attribute\Option setAttributeId(int $value)
 * @method \Magento\Eav\Model\Entity\Attribute\Option setSortOrder(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends AbstractExtensibleModel implements AttributeOptionInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Attribute\Option');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnoreStart
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
    //@codeCoverageIgnoreEnd
}
