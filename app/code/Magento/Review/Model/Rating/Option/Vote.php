<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Rating\Option;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\ReviewApi\Api\Data\RatingOptionVoteInterface;

/**
 * Rating vote model
 */
class Vote extends AbstractExtensibleModel implements RatingOptionVoteInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Option\Vote::class);
    }

    /**
     * @inheritdoc
     */
    public function getVoteId(): ?int
    {
        return $this->_getData(self::VOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVoteId(?int $voteId): RatingOptionVoteInterface
    {
        $this->setData(self::VOTE_ID, $voteId);
        return $this;
    }

    /**
     * Get option id
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRatingOptionId()
     * @return int
     */
    public function getOptionId()
    {
        return $this->getRatingOptionId();
    }

    /**
     * Set option id
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRatingOptionId()
     * @param int $ratingOptionId
     * @return RatingOptionVoteInterface
     */
    public function setOptionId($ratingOptionId)
    {
        return $this->setRatingOptionId($ratingOptionId);
    }

    /**
     * @inheritdoc
     */
    public function getRatingOptionId(): ?int
    {
        return $this->_getData(self::RATING_OPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRatingOptionId(int $ratingOptionId): RatingOptionVoteInterface
    {
        $this->setData(self::RATING_OPTION_ID, $ratingOptionId);
        return $this;
    }

    /**
     * Get remote ip
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRemoteIpAddress()
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->getRemoteIpAddress();
    }

    /**
     * Set remote ip
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRemoteIpAddress()
     * @param string $remoteIp
     * @return RatingOptionVoteInterface
     */
    public function setRemoteIp($remoteIp)
    {
        return $this->setRemoteIpAddress($remoteIp);
    }

    /**
     * @inheritdoc
     */
    public function getRemoteIpAddress(): ?string
    {
        return $this->_getData(self::REMOTE_IP_ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setRemoteIpAddress(string $ipAddress): RatingOptionVoteInterface
    {
        $this->setData(self::REMOTE_IP_ADDRESS, $ipAddress);
        return $this;
    }

    /**
     * Get remote ip long
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRemoteIpAddressLong()
     * @return string
     */
    public function getRemoteIpLong()
    {
        return $this->getRemoteIpAddressLong();
    }

    /**
     * Set remote ip long
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRemoteIpAddressLong()
     * @param string $remoteIpLong
     * @return RatingOptionVoteInterface
     */
    public function setRemoteIpLong($remoteIpLong)
    {
        return $this->setRemoteIpAddressLong($remoteIpLong);
    }

    /**
     * @inheritdoc
     */
    public function getRemoteIpAddressLong(): ?string
    {
        return $this->_getData(self::REMOTE_IP_ADDRESS_LONG);
    }

    /**
     * @inheritdoc
     */
    public function setRemoteIpAddressLong(string $ipAddressLong): RatingOptionVoteInterface
    {
        $this->setData(self::REMOTE_IP_ADDRESS_LONG, $ipAddressLong);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): ?int
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(?int $customerId): RatingOptionVoteInterface
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * Get entity pk value
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRelatedEntityId()
     * @return int
     */
    public function getEntityPkValue()
    {
        return $this->getRelatedEntityId();
    }

    /**
     * Set entity pk value
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRelatedEntityId()
     * @param int $entityPkValue
     * @return RatingOptionVoteInterface
     */
    public function setEntityPkValue($entityPkValue)
    {
        return $this->setRelatedEntityId($entityPkValue);
    }

    /**
     * @inheritdoc
     */
    public function getRelatedEntityId(): ?int
    {
        return $this->_getData(self::RELATED_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRelatedEntityId(int $entityId): RatingOptionVoteInterface
    {
        $this->setData(self::RELATED_ENTITY_ID, $entityId);
        return $this;
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
    public function setRatingId(int $ratingId): RatingOptionVoteInterface
    {
        $this->setData(self::RATING_ID, $ratingId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewId(): ?int
    {
        return $this->_getData(self::REVIEW_ID);
    }

    /**
     * @inheritdoc
     */
    public function setReviewId(int $reviewId): RatingOptionVoteInterface
    {
        $this->setData(self::REVIEW_ID, $reviewId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPercent(): ?float
    {
        return $this->_getData(self::PERCENT);
    }

    /**
     * @inheritdoc
     */
    public function setPercent(float $percent): RatingOptionVoteInterface
    {
        $this->setData(self::PERCENT, $percent);
        return $this;
    }

    /**
     * Get rating code
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRatingName()
     * @return string
     */
    public function getRatingCode()
    {
        return $this->getRatingName();
    }

    /**
     * Set rating code
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRatingName()
     * @param string $ratingCode
     * @return RatingOptionVoteInterface
     */
    public function setRatingCode($ratingCode)
    {
        return $this->setRatingName($ratingCode);
    }

    /**
     * @inheritdoc
     */
    public function getRatingName(): ?string
    {
        return $this->_getData(self::RATING_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setRatingName(?string $ratingName): RatingOptionVoteInterface
    {
        $this->setData(self::RATING_NAME, $ratingName);
        return $this;
    }

    /**
     * Get value
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::getRatingValue()
     * @return int
     */
    public function getValue()
    {
        return $this->getRatingValue();
    }

    /**
     * Set value
     *
     * @deprecated
     * @see \Magento\Review\Model\Rating\Option\Vote::setRatingValue()
     * @param int $value
     * @return RatingOptionVoteInterface
     */
    public function setValue($value)
    {
        return $this->setRatingValue($value);
    }

    /**
     * @inheritdoc
     */
    public function getRatingValue(): int
    {
        return $this->_getData(self::RATING_VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setRatingValue(int $value): RatingOptionVoteInterface
    {
        $this->setData(self::RATING_VALUE, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        return $this->_getData(self::STORE_ID) ? (int)$this->_getData(self::STORE_ID) : null;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId): RatingOptionVoteInterface
    {
        $this->setData(self::STORE_ID, $storeId);
        return$this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->extensionAttributesFactory->create(RatingOptionVoteInterface::class);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingOptionVoteExtensionInterface $extensionAttributes
    ): RatingOptionVoteInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
