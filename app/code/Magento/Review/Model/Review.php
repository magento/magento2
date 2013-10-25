<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Review
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review model
 *
 * @method \Magento\Review\Model\Resource\Review _getResource()
 * @method \Magento\Review\Model\Resource\Review getResource()
 * @method string getCreatedAt()
 * @method \Magento\Review\Model\Review setCreatedAt(string $value)
 * @method \Magento\Review\Model\Review setEntityId(int $value)
 * @method int getEntityPkValue()
 * @method \Magento\Review\Model\Review setEntityPkValue(int $value)
 * @method int getStatusId()
 * @method \Magento\Review\Model\Review setStatusId(int $value)
 *
 * @category    Magento
 * @package     Magento_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Model;

class Review extends \Magento\Core\Model\AbstractModel
{
    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'review';

    /**
     * Review entity codes
     *
     */
    const ENTITY_PRODUCT_CODE   = 'product';
    const ENTITY_CUSTOMER_CODE  = 'customer';
    const ENTITY_CATEGORY_CODE  = 'category';

    const STATUS_APPROVED       = 1;
    const STATUS_PENDING        = 2;
    const STATUS_NOT_APPROVED   = 3;

    /**
     * @var \Magento\Review\Model\Resource\Review\Product\CollectionFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Review\Model\Resource\Review\Status\CollectionFactory
     */
    protected $_statusFactory;

    /**
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryFactory;

    /**
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryModFactory;

    /**
     * @var \Magento\Review\Model\Review\Summary
     */
    protected $_reviewSummary;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlModel;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Review\Model\Resource\Review\Product\CollectionFactory $productFactory
     * @param \Magento\Review\Model\Resource\Review\Status\CollectionFactory $statusFactory
     * @param \Magento\Review\Model\Resource\Review\Summary\CollectionFactory $summaryFactory
     * @param \Magento\Review\Model\Review\SummaryFactory $summaryModFactory
     * @param \Magento\Review\Model\Review\Summary $reviewSummary
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlInterface $urlModel
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Review\Model\Resource\Review\Product\CollectionFactory $productFactory,
        \Magento\Review\Model\Resource\Review\Status\CollectionFactory $statusFactory,
        \Magento\Review\Model\Resource\Review\Summary\CollectionFactory $summaryFactory,
        \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
        \Magento\Review\Model\Review\Summary $reviewSummary,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\UrlInterface $urlModel,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        $this->_statusFactory = $statusFactory;
        $this->_summaryFactory = $summaryFactory;
        $this->_summaryModFactory = $summaryModFactory;
        $this->_reviewSummary = $reviewSummary;
        $this->_storeManager = $storeManager;
        $this->_urlModel = $urlModel;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Review');
    }

    public function getProductCollection()
    {
        return $this->_productFactory->create();
    }

    public function getStatusCollection()
    {
        return $this->_statusFactory->create();
    }

    public function getTotalReviews($entityPkValue, $approvedOnly=false, $storeId=0)
    {
        return $this->getResource()->getTotalReviews($entityPkValue, $approvedOnly, $storeId);
    }

    public function aggregate()
    {
        $this->getResource()->aggregate($this);
        return $this;
    }

    public function getEntitySummary($product, $storeId=0)
    {
        $summaryData = $this->_summaryModFactory->create()
            ->setStoreId($storeId)
            ->load($product->getId());
        $summary = new \Magento\Object();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }

    public function getPendingStatus()
    {
        return self::STATUS_PENDING;
    }

    public function getReviewUrl()
    {
        return $this->_urlModel->getUrl('review/product/view', array('id' => $this->getReviewId()));
    }

    public function validate()
    {
        $errors = array();

        if (!\Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = __('The review summary field can\'t be empty.');
        }

        if (!\Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = __('The nickname field can\'t be empty.');
        }

        if (!\Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
            $errors[] = __('The review field can\'t be empty.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Perform actions after object delete
     *
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _afterDeleteCommit()
    {
        $this->getResource()->afterDeleteCommit($this);
        return parent::_afterDeleteCommit();
    }

    /**
     * Append review summary to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return \Magento\Review\Model\Review
     */
    public function appendSummary($collection)
    {
        $entityIds = array();
        foreach ($collection->getItems() as $_item) {
            $entityIds[] = $_item->getEntityId();
        }

        if (sizeof($entityIds) == 0) {
            return $this;
        }

        $summaryData = $this->_summaryFactory->create()
            ->addEntityFilter($entityIds)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->load();

        foreach ($collection->getItems() as $_item ) {
            foreach ($summaryData as $_summary) {
                if ($_summary->getEntityPkValue() == $_item->getEntityId()) {
                    $_item->setRatingSummary($_summary);
                }
            }
        }

        return $this;
    }

    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * Check if current review approved or not
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatusId() == self::STATUS_APPROVED;
    }

    /**
     * Check if current review available on passed store
     *
     * @param int|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isAvailableOnStore($store = null)
    {
        $store = $this->_storeManager->getStore($store);
        if ($store) {
            return in_array($store->getId(), (array)$this->getStores());
        }

        return false;
    }

    /**
     * Get review entity type id by code
     *
     * @param string $entityCode
     * @return int|bool
     */
    public function getEntityIdByCode($entityCode)
    {
        return $this->getResource()->getEntityIdByCode($entityCode);
    }
}
