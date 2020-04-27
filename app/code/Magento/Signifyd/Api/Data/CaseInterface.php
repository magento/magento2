<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api\Data;

use Magento\Signifyd\Model\SignifydGateway\Gateway;

/**
 * Signifyd Case entity interface
 *
 * @api
 * @see https://www.signifyd.com/docs/api/#/reference/cases/retrieve-a-case/get-a-case
 * @since 100.2.0
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
interface CaseInterface
{
    /**#@+
     * Constants for case available statuses
     */
    const STATUS_OPEN = Gateway::STATUS_OPEN;
    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = Gateway::STATUS_PROCESSING;
    const STATUS_FLAGGED = Gateway::STATUS_FLAGGED;
    const STATUS_DISMISSED = Gateway::STATUS_DISMISSED;
    /**#@-*/

    /**#@+
     * Constants for guarantee available statuses
     */
    const GUARANTEE_APPROVED = Gateway::GUARANTEE_APPROVED;
    const GUARANTEE_DECLINED = Gateway::GUARANTEE_DECLINED;
    const GUARANTEE_PENDING = Gateway::GUARANTEE_PENDING;
    const GUARANTEE_CANCELED = Gateway::GUARANTEE_CANCELED;
    const GUARANTEE_IN_REVIEW = Gateway::GUARANTEE_IN_REVIEW;
    const GUARANTEE_UNREQUESTED = Gateway::GUARANTEE_UNREQUESTED;
    /**#@-*/

    /**#@+
     * Constants for case available review dispositions
     */
    const DISPOSITION_GOOD = Gateway::DISPOSITION_GOOD;
    const DISPOSITION_FRAUDULENT = Gateway::DISPOSITION_FRAUDULENT;
    const DISPOSITION_UNSET = Gateway::DISPOSITION_UNSET;
    /**#@-*/

    /**
     * Returns local case entity identifier.
     *
     * @return int
     * @since 100.2.0
     */
    public function getEntityId();

    /**
     * Sets local case entity id.
     *
     * @param int $id
     * @return $this
     * @since 100.2.0
     */
    public function setEntityId($id);

    /**
     * Returns Signifyd case identifier.
     *
     * @return int
     * @since 100.2.0
     */
    public function getCaseId();

    /**
     * Sets Signifyd case id.
     *
     * @param int $id
     * @return $this
     * @since 100.2.0
     */
    public function setCaseId($id);

    /**
     * Returns value, which indicates if a guarantee can be requested for a case.
     * Returns null if state of guarantee eligible does not set yet.
     *
     * @return boolean|null
     * @since 100.2.0
     */
    public function isGuaranteeEligible();

    /**
     * Sets value-indicator about guarantee availability for a case.
     *
     * @param bool $guaranteeEligible
     * @return $this
     * @since 100.2.0
     */
    public function setGuaranteeEligible($guaranteeEligible);

    /**
     * Returns decision state of the guarantee.
     *
     * @return string
     * @since 100.2.0
     */
    public function getGuaranteeDisposition();

    /**
     * Sets decision state of the guarantee.
     *
     * @param string $disposition
     * @return $this
     * @since 100.2.0
     */
    public function setGuaranteeDisposition($disposition);

    /**
     * Returns case status.
     *
     * @return string
     * @since 100.2.0
     */
    public function getStatus();

    /**
     * Sets case status.
     *
     * @param string $status
     * @return $this
     * @since 100.2.0
     */
    public function setStatus($status);

    /**
     * Returns value, which indicates the likelihood that the order is fraud.
     *
     * @return int
     * @since 100.2.0
     */
    public function getScore();

    /**
     * Sets risk level value.
     *
     * @param int $score
     * @return $this
     * @since 100.2.0
     */
    public function setScore($score);

    /**
     * Get order id for a case.
     *
     * @return int
     * @since 100.2.0
     */
    public function getOrderId();

    /**
     * Sets order id for a case.
     *
     * @param int $orderId
     * @return $this
     * @since 100.2.0
     */
    public function setOrderId($orderId);

    /**
     * Returns data about a team associated with a case.
     *
     * @return array
     * @since 100.2.0
     */
    public function getAssociatedTeam();

    /**
     * Sets team data associated with a case.
     *
     * @param array $team
     * @return $this
     * @since 100.2.0
     */
    public function setAssociatedTeam(array $team);

    /**
     * Returns disposition of an agent's opinion after reviewing the case.
     *
     * @return string
     * @since 100.2.0
     */
    public function getReviewDisposition();

    /**
     * Sets case disposition.
     *
     * @param string $disposition
     * @return $this
     * @since 100.2.0
     */
    public function setReviewDisposition($disposition);

    /**
     * Returns creation datetime for a case.
     *
     * @return string
     * @since 100.2.0
     */
    public function getCreatedAt();

    /**
     * Sets creation datetime for a case.
     *
     * @param string $datetime in DATE_ATOM format
     * @return $this
     * @since 100.2.0
     */
    public function setCreatedAt($datetime);

    /**
     * Returns updating datetime for a case.
     *
     * @return string
     * @since 100.2.0
     */
    public function getUpdatedAt();

    /**
     * Sets updating datetime for a case.
     *
     * @param string $datetime in DATE_ATOM format
     * @return $this
     * @since 100.2.0
     */
    public function setUpdatedAt($datetime);
}
