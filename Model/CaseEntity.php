<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Implementation of Signifyd Case interface.
 * @since 2.2.0
 */
class CaseEntity extends AbstractModel implements CaseInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    protected $_eventPrefix = 'signifyd_case';

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * CaseEntity constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SerializerInterface $serializer
     * @param array $data
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SerializerInterface $serializer,
        array $data = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\CaseEntity::class);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getEntityId()
    {
        return (int) $this->getData('entity_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setEntityId($id)
    {
        $this->setData('entity_id', (int) $id);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getCaseId()
    {
        return (int) $this->getData('case_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setCaseId($id)
    {
        $this->setData('case_id', (int) $id);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function isGuaranteeEligible()
    {
        $value = $this->getData('guarantee_eligible');
        return ($value === null) ? $value : (bool) $value;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setGuaranteeEligible($guaranteeEligible)
    {
        $this->setData('guarantee_eligible', $guaranteeEligible);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getGuaranteeDisposition()
    {
        return (string) $this->getData('guarantee_disposition');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setGuaranteeDisposition($disposition)
    {
        $this->setData('guarantee_disposition', (string) $disposition);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getStatus()
    {
        return (string) $this->getData('status');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setStatus($status)
    {
        $this->setData('status', (string) $status);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getScore()
    {
        return (int) $this->getData('score');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setScore($score)
    {
        $this->setData('score', (int) $score);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getOrderId()
    {
        return (int) $this->getData('order_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setOrderId($orderId)
    {
        $this->setData('order_id', (int) $orderId);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAssociatedTeam()
    {
        $teamData = $this->getData('associated_team');
        return empty($teamData) ? [] : $this->serializer->unserialize($teamData);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAssociatedTeam(array $team)
    {
        $this->setData('associated_team', $this->serializer->serialize($team));
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getReviewDisposition()
    {
        return (string) $this->getData('review_disposition');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setReviewDisposition($disposition)
    {
        $this->setData('review_disposition', (string) $disposition);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setCreatedAt($datetime)
    {
        $this->setData('created_at', $datetime);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setUpdatedAt($datetime)
    {
        $this->setData('updated_at', $datetime);
        return $this;
    }
}
