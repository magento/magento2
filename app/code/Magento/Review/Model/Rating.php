<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model;

/**
 * Rating model
 *
 * @method Resource\Rating getResource()
 * @method Resource\Rating _getResource()
 * @method array getRatingCodes()
 * @method \Magento\Review\Model\Rating setRatingCodes(array $value)
 * @method array getStores()
 * @method \Magento\Review\Model\Rating setStores(array $value)
 * @method string getRatingCode()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rating extends \Magento\Framework\Model\AbstractModel
{
    /**
     * rating entity codes
     */
    const ENTITY_PRODUCT_CODE = 'product';

    const ENTITY_PRODUCT_REVIEW_CODE = 'product_review';

    const ENTITY_REVIEW_CODE = 'review';

    /**
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    protected $_ratingOptionFactory;

    /**
     * @var \Magento\Review\Model\Resource\Rating\Option\CollectionFactory
     */
    protected $_ratingCollectionF;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\Rating\OptionFactory $ratingOptionFactory
     * @param \Magento\Review\Model\Resource\Rating\Option\CollectionFactory $ratingCollectionF
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\Rating\OptionFactory $ratingOptionFactory,
        \Magento\Review\Model\Resource\Rating\Option\CollectionFactory $ratingCollectionF,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_ratingOptionFactory = $ratingOptionFactory;
        $this->_ratingCollectionF = $ratingCollectionF;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Rating');
    }

    /**
     * @param int $optionId
     * @param int $entityPkValue
     * @return $this
     */
    public function addOptionVote($optionId, $entityPkValue)
    {
        $this->_ratingOptionFactory->create()->setOptionId(
            $optionId
        )->setRatingId(
            $this->getId()
        )->setReviewId(
            $this->getReviewId()
        )->setEntityPkValue(
            $entityPkValue
        )->addVote();
        return $this;
    }

    /**
     * @param int $optionId
     * @return $this
     */
    public function updateOptionVote($optionId)
    {
        $this->_ratingOptionFactory->create()->setOptionId(
            $optionId
        )->setVoteId(
            $this->getVoteId()
        )->setReviewId(
            $this->getReviewId()
        )->setDoUpdate(
            1
        )->addVote();
        return $this;
    }

    /**
     * retrieve rating options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = $this->getData('options');
        if ($options) {
            return $options;
        } elseif ($this->getId()) {
            return $this->_ratingCollectionF->create()->addRatingFilter(
                $this->getId()
            )->setPositionOrder()->load()->getItems();
        }
        return [];
    }

    /**
     * Get rating collection object
     *
     * @param int $entityPkValue
     * @param bool $onlyForCurrentStore
     * @return \Magento\Framework\Data\Collection\Db
     */
    public function getEntitySummary($entityPkValue, $onlyForCurrentStore = true)
    {
        $this->setEntityPkValue($entityPkValue);
        return $this->_getResource()->getEntitySummary($this, $onlyForCurrentStore);
    }

    /**
     * @param int $reviewId
     * @param bool $onlyForCurrentStore
     * @return array
     */
    public function getReviewSummary($reviewId, $onlyForCurrentStore = true)
    {
        $this->setReviewId($reviewId);
        return $this->_getResource()->getReviewSummary($this, $onlyForCurrentStore);
    }

    /**
     * Get rating entity type id by code
     *
     * @param string $entityCode
     * @return int
     */
    public function getEntityIdByCode($entityCode)
    {
        return $this->getResource()->getEntityIdByCode($entityCode);
    }
}
