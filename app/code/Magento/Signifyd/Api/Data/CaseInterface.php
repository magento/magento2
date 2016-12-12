<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api\Data;

/**
 * Interface Signifyd Case entity
 *
 * @api
 * @see https://www.signifyd.com/docs/api/#/reference/cases/retrieve-a-case/get-a-case
 */
interface CaseInterface
{
    /**#@+
     * Constants for case available statuses
     */
    /**
     * Open status
     */
    const STATUS_OPEN = 'OPEN';

    /**
     * Processing status
     */
    const STATUS_PROCESSING = 'PROCESSING';

    /**
     * Flagged status
     */
    const STATUS_FLAGGED = 'FLAGGED';

    /**
     * Dismissed status
     */
    const STATUS_DISMISSED = 'DISMISSED';

    /**#@+
     * Constants for guarantee available statuses
     */
    /**
     * Approved status
     */
    const GUARANTEE_APPROVED = 'APPROVED';

    /**
     * Declined status
     */
    const GUARANTEE_DECLINED = 'DECLINED';

    /**
     * Pending status
     */
    const GUARANTEE_PENDING = 'PENDING';

    /**
     * Canceled status
     */
    const GUARANTEE_CANCELED = 'CANCELED';

    /**
     * In review status
     */
    const GUARANTEE_IN_REVIEW = 'IN_REVIEW';

    /**#@+
     * Constants for case available review dispositions
     */
    /**
     * Review disposition is good
     */
    const DISPOSITION_GOOD = 'GOOD';

    /**
     * Review disposition is fraud
     */
    const DISPOSITION_FRAUDULENT = 'FRAUDULENT';

    /**
     * Review disposition is not set
     */
    const DISPOSITION_UNSET = 'UNSET';

    /**
     * Gets case entity id
     * @return int
     */
    public function getEntityId();

    /**
     * Sets case entity id
     * @param int $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * Gets Signifyd case id
     * @return int
     */
    public function getCaseId();

    /**
     * Sets Signifyd case id
     * @param int $id
     * @return $this
     */
    public function setCaseId($id);

    /**
     * Gets value, which indicates if a guarantee can be requested for a case
     * @return boolean
     */
    public function getGuaranteeEligible();

    /**
     * Sets value-indicator about guarantee availability for a case
     * @param bool $guaranteeEligible
     * @return $this
     */
    public function setGuaranteeEligible($guaranteeEligible);

    /**
     * Gets decision state of the guarantee
     * @return string
     */
    public function getGuaranteeDisposition();

    /**
     * Sets decision state of the guarantee
     * @param string $disposition
     * @return $this
     */
    public function setGuaranteeDisposition($disposition);

    /**
     * Gets case status
     * @return string
     */
    public function getStatus();

    /**
     * Sets case status
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Gets value, which indicates the likelihood that the order is fraud
     * @return int
     */
    public function getScore();

    /**
     * Sets risk level value
     * @param int $score
     * @return $this
     */
    public function setScore($score);

    /**
     * Get order id for a case
     * @return int
     */
    public function getOrderId();

    /**
     * Sets order id for a case
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Gets id of team associated with a case
     * @return int
     */
    public function getAssociatedTeam();

    /**
     * Sets case associated team id
     * @param int $teamId
     * @return $this
     */
    public function setAssociatedTeam($teamId);

    /**
     * Gets disposition of an agent's opinion after reviewing the case
     * @return string
     */
    public function getReviewDisposition();

    /**
     * Sets case disposition
     * @param string $disposition
     * @return $this
     */
    public function setReviewDisposition($disposition);

    /**
     * Gets creation datetime for a case
     * @return string
     */
    public function getCreatedAt();

    /**
     * Sets creation datetime for a case
     * @param string $datetime in DATE_ATOM format
     * @return $this
     */
    public function setCreatedAt($datetime);

    /**
     * Gets updating datetime for a case
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Sets updating datetime for a case
     * @param string $datetime in DATE_ATOM format
     * @return $this
     */
    public function setUpdatedAt($datetime);
}
