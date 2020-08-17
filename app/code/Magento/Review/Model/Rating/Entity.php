<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Rating;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\ReviewApi\Api\Data\RatingEntityInterface;

/**
 * Ratings entity model
 */
class Entity extends AbstractExtensibleModel implements RatingEntityInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Entity::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getEntityCode(): string
    {
        return $this->_getData(self::ENTITY_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setEntityCode(string $entityCode): RatingEntityInterface
    {
        $this->setData(self::ENTITY_CODE, $entityCode);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(RatingEntityInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface $extensionAttributes
    ): RatingEntityInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get entity id by code
     *
     * @param string $entityCode
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdByCode($entityCode)
    {
        return $this->_getResource()->getIdByCode($entityCode);
    }
}
