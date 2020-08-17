<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Rating;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\ReviewApi\Api\Data\RatingOptionInterface;

/**
 * Rating option model
 */
class Option extends AbstractExtensibleModel implements RatingOptionInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Option::class);
    }

    /**
     * @inheritdoc
     */
    public function getOptionId()
    {
        return $this->_getData(self::OPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOptionId($optionId): RatingOptionInterface
    {
        $this->setData(self::OPTION_ID, $optionId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingId(): ?int
    {
        return $this->_getData(self::RATING_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRatingId(int $ratingId): RatingOptionInterface
    {
        $this->setData(self::RATING_ID, $ratingId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): int
    {
        return $this->_getData(self::CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCode(int $code): RatingOptionInterface
    {
        $this->setData(self::CODE, $code);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): int
    {
        return $this->_getData(self::VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue(int $value): RatingOptionInterface
    {
        $this->setData(self::VALUE, $value);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return $this->_getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): RatingOptionInterface
    {
        $this->setData(self::POSITION, $position);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(RatingOptionInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingOptionExtensionInterface $extensionAttributes
    ): RatingOptionInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Add vote
     *
     * @return $this
     */
    public function addVote()
    {
        $this->getResource()->addVote($this);
        return $this;
    }
}
