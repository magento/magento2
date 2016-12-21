<?php
namespace Magento\Review\Model;

use Magento\Review\Api\ReviewInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

class ReviewManagement implements \Magento\Review\Api\ReviewInterface
{

    /**
     * Review collection
     *
     * @var ReviewCollection
     */
    protected $_reviewsCollection;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review Detail table
     *
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * Review status table
     *
     * @var string
     */
    protected $_reviewStatusTable;

    /**
     * Review entity table
     *
     * @var string
     */
    protected $_reviewEntityTable;

    /**
     * Review store table
     *
     * @var string
     */
    protected $_reviewStoreTable;

    /**
     * Review aggregate table
     *
     * @var string
     */
    protected $_aggregateTable;

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Rating resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option
     */
    protected $_ratingOptions;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     * $collectionFactory
     * @param Rating\Option $ratingOptions
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory
        $collectionFactory,
        Rating\Option $ratingOptions
    ) {
        $this->_date = $date;
        $this->_reviewsColFactory = $collectionFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingOptions = $ratingOptions;
    }

    /**
     * Get reviews of the product
     * @param int $productId
     * @return []|bool
     */

    public function getReviewsList($productId)
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewsColFactory->create()
                ->addStatusFilter(
                    \Magento\Review\Model\Review::STATUS_APPROVED
                )->addEntityFilter(
                    'product',
                    $productId
                )->setDateOrder();
        }
        $reviewArray = [];
        $reviewCollection = $this->_reviewsCollection;
        $collection = $reviewCollection->load()->addRateVotes();
        $reviewArray[] = count($collection);
        foreach ($collection as $reviewCollection) {
            $rating = $reviewCollection->getRatingVotes()->getData();
            $data = [
                "review_id"       => $reviewCollection->getReviewId(),
                "created_at"      => $reviewCollection->getCreatedAt(),
                "entity_id"       => $reviewCollection->getEntityId(),
                "entity_pk_value" => $reviewCollection->getEntityPkValue(),
                "status_id"       => $reviewCollection->getStatusId(),
                "detail_id"       => $reviewCollection->getDetailId(),
                "title"           => $reviewCollection->getTitle(),
                "detail"          => $reviewCollection->getDetail(),
                "nickname"        => $reviewCollection->getNickname(),
                "customer_id"     => $reviewCollection->getCustomerId(),
                "entity_code"     => $reviewCollection->getEntityCode(),
                "title"           => $reviewCollection->getTitle(),
                "ravitng_votes"   => $rating
            ];
            $reviewArray[] = $data;
        }
        return $reviewArray;
    }

    /**
     * Added review item.
     * @param int $productId
     * @param string $title
     * @param string $nickname
     * @param string $detail
     * @param int $ratingValue
     * @param int $customerId
     * @param int $storeId
     * @return boolean
     */

    public function writeReviews(
        $productId,
        $nickname,
        $title,
        $detail,
        $ratingValue,
        $customerId = null,
        $storeId = 1
    ) {

        $data = [
            "nickname" => $nickname,
            "title"    => $title,
            "detail"   => $detail
        ];
        $rating = ["1" => $ratingValue];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager
            ->get('Magento\Catalog\Model\Product')->load($productId);
        
        if (($product) && !empty($data)) {
            $review = $this->_reviewFactory->create()->setData($data);
            $review->unsetData('review_id');

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId(
                        $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE)
                    )
                    ->setEntityPkValue($product->getId())
                    ->setStatusId(Review::STATUS_PENDING)
                    ->setCustomerId($customerId)
                    ->setStoreId($storeId)
                    ->setStores([$storeId])
                    ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->_ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $status = true;
                    $message = 'You submitted your review for moderation.';
                } catch (\Exception $e) {
                    $message = 'We can\'t post your review right now.';
                    $status = false;
                }
            }
        }
        $response = [
            "status" => $status,
            "message" => $message
        ];

        return $response;
    }
}
