<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseManagement;

/**
 * Get Signifyd Case Info
 */
class CaseInfo extends Template
{
    /**
     * @var CaseInterface
     */
    private $caseEntity = false;

    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @param Context $context
     * @param CaseManagement $caseManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        CaseManagement $caseManagement,
        array $data = []
    ) {
        $this->caseManagement = $caseManagement;

        parent::__construct($context, $data);
    }

    /**
     * Gets case entity associated with order id.
     *
     * @return CaseInterface|null
     */
    private function getCaseEntity()
    {
        if ($this->caseEntity === false) {
            $this->caseEntity = $this->caseManagement->getByOrderId(
                $this->getOrderId()
            );
        }

        return $this->caseEntity;
    }

    /**
     * Default getter for case properties
     *
     * @param mixed $defaultValue
     * @param callable $callback
     * @return mixed
     */
    private function getCaseProperty($defaultValue, callable $callback)
    {
        return $this->isEmptyCase() ? $defaultValue : call_user_func($callback);
    }

    /**
     * Checks if case is exists for order
     *
     * @return bool
     */
    public function isEmptyCase()
    {
        return $this->getCaseEntity() === null;
    }

    /**
     * Gets case status
     *
     * @return string
     */
    public function getCaseStatus()
    {
        return $this->getCaseProperty('', function () {
            $caseStatusMap = [
                CaseInterface::STATUS_OPEN => __('Open'),
                CaseInterface::STATUS_PENDING => __('Pending'),
                CaseInterface::STATUS_PROCESSING => __('Processing'),
                CaseInterface::STATUS_FLAGGED => __('Flagged'),
                CaseInterface::STATUS_DISMISSED => __('Dismissed')
            ];

            $status = isset($caseStatusMap[$this->getCaseEntity()->getStatus()]) ?
                $caseStatusMap[$this->getCaseEntity()->getStatus()] :
                '';

            return $status;
        });
    }

    /**
     * Gets case guarantee disposition status.
     *
     * @return string
     */
    public function getCaseGuaranteeDisposition()
    {
        return $this->getCaseProperty('', function () {
            $guaranteeStatusMap = [
                CaseInterface::GUARANTEE_APPROVED => __('Approved'),
                CaseInterface::GUARANTEE_DECLINED => __('Declined'),
                CaseInterface::GUARANTEE_PENDING => __('Pending'),
                CaseInterface::GUARANTEE_CANCELED => __('Canceled'),
                CaseInterface::GUARANTEE_IN_REVIEW => __('In Review'),
                CaseInterface::GUARANTEE_UNREQUESTED => __('Unrequested')
            ];

            $status = isset($guaranteeStatusMap[$this->getCaseEntity()->getGuaranteeDisposition()]) ?
                $guaranteeStatusMap[$this->getCaseEntity()->getGuaranteeDisposition()] :
                '';

            return $status;
        });
    }

    /**
     * Gets case review disposition status.
     *
     * @return string
     */
    public function getCaseReviewDisposition()
    {
        return $this->getCaseProperty('', function () {
            $reviewStatusMap = [
                CaseInterface::DISPOSITION_GOOD => __('Good'),
                CaseInterface::DISPOSITION_FRAUDULENT => __('Fraudulent'),
                CaseInterface::DISPOSITION_UNSET => __('Unset')
            ];

            $status = isset($reviewStatusMap[$this->getCaseEntity()->getReviewDisposition()]) ?
                $reviewStatusMap[$this->getCaseEntity()->getReviewDisposition()] :
                '';

            return $status;
        });
    }

    /**
     * Retrieves current order Id.
     *
     * @return integer
     */
    private function getOrderId()
    {
        return (int) $this->getRequest()->getParam('order_id');
    }
}
