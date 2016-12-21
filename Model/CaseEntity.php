<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Implementation of Signifyd Case interface
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
    protected $serializer;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->serializer = ObjectManager::getInstance()->get(SerializerInterface::class);
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
        return $this->getData('guarantee_eligible');
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
