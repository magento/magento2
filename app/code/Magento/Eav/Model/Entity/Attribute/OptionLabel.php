<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Entity attribute option label model
 *
 * @codeCoverageIgnore
 */
class OptionLabel extends AbstractModel implements AttributeOptionLabelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
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
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
