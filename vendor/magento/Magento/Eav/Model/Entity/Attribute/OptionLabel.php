<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Entity attribute option label model
 *
 */
class OptionLabel extends AbstractExtensibleModel implements AttributeOptionLabelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }
}
