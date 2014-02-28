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
namespace Magento\Review\Block;

/**
 * Review detailed view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * View template name
     *
     * @var string
     */
    protected $_template = 'view.phtml';

    /**
     * Rating option model
     *
     * @var \Magento\Rating\Model\Rating\Option\VoteFactory
     */
    protected $_voteFactory;

    /**
     * Rating model factory
     *
     * @var \Magento\Rating\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $compareProduct
     * @param \Magento\Theme\Helper\Layout $layoutHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Rating\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Rating\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param array $data
     * @param array $priceBlockTypes
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Theme\Helper\Layout $layoutHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Rating\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Rating\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->_voteFactory = $voteFactory;
        $this->_reviewFactory = $reviewFactory;

        parent::__construct(
            $context,
            $catalogConfig,
            $registry,
            $taxData,
            $catalogData,
            $mathRandom,
            $cartHelper,
            $wishlistHelper,
            $compareProduct,
            $layoutHelper,
            $imageHelper,
            $data,
            $priceBlockTypes
        );
    }

    /**
     * Retrieve current product model from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductData()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve current review model from registry
     *
     * @return \Magento\Review\Model\Review
     */
    public function getReviewData()
    {
        return $this->_coreRegistry->registry('current_review');
    }

    /**
     * Prepare link to review list for current product
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/list', array('id' => $this->getProductData()->getId()));
    }

    /**
     * Retrieve collection of ratings
     *
     * @return \Magento\Rating\Model\Resource\Rating\Option\Vote\Collection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            $ratingCollection = $this->_voteFactory->create()
                ->getResourceCollection()
                ->setReviewFilter($this->getReviewId())
                ->setStoreFilter($this->_storeManager->getStore()->getId())
                ->addRatingInfo($this->_storeManager->getStore()->getId())
                ->load();
            $this->setRatingCollection( ( $ratingCollection->getSize() ) ? $ratingCollection : false );
        }
        return $this->getRatingCollection();
    }

    /**
     * Retrieve rating summary for current product
     *
     * @return string
     */
    public function getRatingSummary()
    {
        if (!$this->getRatingSummaryCache()) {
            $this->setRatingSummaryCache(
                $this->_ratingFactory->create()->getEntitySummary($this->getProductData()->getId())
            );
        }
        return $this->getRatingSummaryCache();
    }

    /**
     * Retrieve total review count for current product
     *
     * @return string
     */
    public function getTotalReviews()
    {
        if (!$this->getTotalReviewsCache()) {
            $this->setTotalReviewsCache(
                $this->_reviewFactory->create()->getTotalReviews(
                    $this->getProductData()->getId(), false, $this->_storeManager->getStore()->getId()
                )
            );
        }
        return $this->getTotalReviewsCache();
    }

    /**
     * Format date in long format
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_LONG);
    }
}
