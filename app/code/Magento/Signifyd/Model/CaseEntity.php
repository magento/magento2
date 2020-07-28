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
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class CaseEntity extends AbstractModel implements CaseInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'signifyd_case';

    /**
     * @var SerializerInterface
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
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\CaseEntity::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return (int) $this->getData('entity_id');
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($id)
    {
        $this->setData('entity_id', (int) $id);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCaseId()
    {
        return (int) $this->getData('case_id');
    }

    /**
     * @inheritdoc
     */
    public function setCaseId($id)
    {
        $this->setData('case_id', (int) $id);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isGuaranteeEligible()
    {
        $value = $this->getData('guarantee_eligible');
        return ($value === null) ? $value : (bool) $value;
    }

    /**
     * @inheritdoc
     */
    public function setGuaranteeEligible($guaranteeEligible)
    {
        $this->setData('guarantee_eligible', $guaranteeEligible);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGuaranteeDisposition()
    {
        return (string) $this->getData('guarantee_disposition');
    }

    /**
     * @inheritdoc
     */
    public function setGuaranteeDisposition($disposition)
    {
        $this->setData('guarantee_disposition', (string) $disposition);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return (string) $this->getData('status');
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData('status', (string) $status);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScore()
    {
        return (int) $this->getData('score');
    }

    /**
     * @inheritdoc
     */
    public function setScore($score)
    {
        $this->setData('score', (int) $score);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return (int) $this->getData('order_id');
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->setData('order_id', (int) $orderId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAssociatedTeam()
    {
        $teamData = $this->getData('associated_team');
        return empty($teamData) ? [] : $this->serializer->unserialize($teamData);
    }

    /**
     * @inheritdoc
     */
    public function setAssociatedTeam(array $team)
    {
        $this->setData('associated_team', $this->serializer->serialize($team));
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewDisposition()
    {
        return (string) $this->getData('review_disposition');
    }

    /**
     * @inheritdoc
     */
    public function setReviewDisposition($disposition)
    {
        $this->setData('review_disposition', (string) $disposition);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($datetime)
    {
        $this->setData('created_at', $datetime);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($datetime)
    {
        $this->setData('updated_at', $datetime);
        return $this;
    }
}
