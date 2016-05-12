<?php
namespace Magento\Review\Api\Data;

interface ReviewInterface
{
    /**
     * Get Review Id
     * 
     * @return integer
     */
    public function getReviewId();

    /**
     * Set Review Id
     * 
     * @param integer $reviewId
     * @return \Magento\Review\Model\Review
     */
    public function setReviewId($reviewId);

    /**
     * Get entity id
     * 
     * @return integer
     */
    public function getEntityId();

    /**
     * Set entity id
     * 
     * @param integer $entityId
     * @return \Magento\Review\Model\Review
     */
    public function setEntityId($entityId);

    /**
     * Get Detail ID
     * 
     * @return integer
     */
    public function getDetailId();

    /**
     * Set Detail ID
     * 
     * @param integer $detailId
     * @return \Magento\Review\Model\Review
     */
    public function setDetailId($detailId);

    /**
     * Get Title
     * 
     * @return string|null
     */
    public function getTitle();

    /**
     * Set Title 
     * 
     * @param string $title
     * @return \Magento\Review\Model\Review
     */
    public function setTitle($title);

    /**
     * Get Detail
     *
     * @return string
     */
    public function getDetail();

    /**
     * Set Detail
     * 
     * @param string $detail
     * @return \Magento\Review\Model\Review
     */
    public function setDetail($detail);

    /**
     * Get Nickname
     * 
     * @return string
     */
    public function getNickname();

    /**
     * Set Nickname
     * 
     * @param string $nickname
     * @return \Magento\Review\Model\Review
     */
    public function setNickname($nickname);

    /**
     * Get Customer ID
     * 
     * @return integer
     */
    public function getCustomerId();

    /**
     * Set Customer ID 
     * 
     * @param integer $customerId
     * @return \Magento\Review\Model\Review
     */
    public function setCustomerId($customerId);

    /**
     * Gets the status code of the review.
     * 
     * @return string
     */
    public function getStatus();

    /**
     * Sets the status ID based on the status code.
     *
     * @param string $statusCode
     * @return $this
     * @throws NoSuchEntityException on invalid status set
     */
    public function setStatus($statusCode);
}
