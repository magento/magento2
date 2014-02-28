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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Model;

use Magento\Review\Model\Resource\Review\Product\Collection as ProductCollection;
use Magento\Review\Model\Resource\Review\Status\Collection as StatusCollection;
use Magento\Catalog\Model\Product;

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
     */
    const ENTITY_PRODUCT_CODE   = 'product';
    const ENTITY_CUSTOMER_CODE  = 'customer';
    const ENTITY_CATEGORY_CODE  = 'category';

    const STATUS_APPROVED       = 1;
    const STATUS_PENDING        = 2;
    const STATUS_NOT_APPROVED   = 3;

    /**
     * Review product collection factory
     *
     * @var \Magento\Review\Model\Resource\Review\Product\CollectionFactory
     */
    protected $_productFactory;

    /**
     * Review status collection factory
     *
     * @var \Magento\Review\Model\Resource\Review\Status\CollectionFactory
     */
    protected $_statusFactory;

    /**
     * Review model summary factory
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryFactory;

    /**
     * Review model summary factory
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_summaryModFactory;

    /**
     * Review model summary
     *
     * @var \Magento\Review\Model\Review\Summary
     */
    protected $_reviewSummary;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Url interface
     *
     * @var \Magento\UrlInterface
     */
    protected $_urlModel;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
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
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
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

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Review');
    }

    /**
     * Get product collection
     *
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        return $this->_productFactory->create();
    }

    /**
     * Get status collection
     *
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->_statusFactory->create();
    }

    /**
     * Get total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($entityPkValue, $approvedOnly=false, $storeId=0)
    {
        return $this->getResource()->getTotalReviews($entityPkValue, $approvedOnly, $storeId);
    }

    /**
     * Aggregate reviews
     *
     * @return $this
     */
    public function aggregate()
    {
        $this->getResource()->aggregate($this);
        return $this;
    }

    /**
     * Get entity summary
     *
     * @param Product $product
     * @param int $storeId
     * @return void
     */
    public function getEntitySummary($product, $storeId=0)
    {
        $summaryData = $this->_summaryModFactory->create()
            ->setStoreId($storeId)
            ->load($product->getId());
        $summary = new \Magento\Object();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }

    /**
     * Get pending status
     *
     * @return int
     */
    public function getPendingStatus()
    {
        return self::STATUS_PENDING;
    }

    /**
     * Get review product view url
     *
     * @return string
     */
    public function getReviewUrl()
    {
        return $this->_urlModel->getUrl('review/product/view', array('id' => $this->getReviewId()));
    }

    /**
     * Validate review summary fields
     *
     * @return bool|string[]
     */
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
     * @param ProductCollection $collection
     * @return $this
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

    /**
     * Validate user before delete
     *
     * @return $this
     */
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
