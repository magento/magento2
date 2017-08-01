<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Entity attribute option label model
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class OptionLabel extends AbstractModel implements AttributeOptionLabelInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
