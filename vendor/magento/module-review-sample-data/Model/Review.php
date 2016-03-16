<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReviewSampleData\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Review
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Review
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    protected $ratingOptionsFactory;

    /**
     * @var array
     */
    protected $ratings;

    /**
     * @var int
     */
    protected $ratingProductEntityId;

    /**
     * @var int
     */
    protected $reviewProductEntityId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param CustomerRepositoryInterface $customerAccount
     * @param \Magento\Review\Model\Rating\OptionFactory $ratingOptionsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CustomerRepositoryInterface $customerAccount,
        \Magento\Review\Model\Rating\OptionFactory $ratingOptionsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->ratingFactory = $ratingFactory;
        $this->productCollection = $productCollectionFactory->create()->addAttributeToSelect('sku');
        $this->customerRepository = $customerAccount;
        $this->ratingOptionsFactory = $ratingOptionsFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $storeId = [$this->storeManager->getDefaultStoreView()->getStoreId()];
                $review = $this->prepareReview($row);
                $this->createRating($row['rating_code'], $storeId);
                $productId = $this->getProductIdBySku($row['sku']);

                if (empty($productId)) {
                    continue;
                }
                /** @var \Magento\Review\Model\ResourceModel\Review\Collection $reviewCollection */
                $reviewCollection = $this->reviewCollectionFactory->create();
                $reviewCollection->addFilter('entity_pk_value', $productId)
                    ->addFilter('entity_id', $this->getReviewEntityId())
                    ->addFieldToFilter('detail.title', ['eq' => $row['title']]);
                if ($reviewCollection->getSize() > 0) {
                    continue;
                }

                if (!empty($row['email']) && ($this->getCustomerIdByEmail($row['email']) != null)) {
                    $review->setCustomerId($this->getCustomerIdByEmail($row['email']));
                }
                $review->save();
                $this->setReviewRating($review, $row);
            }
        }
    }

    /**
     * Retrieve product ID by sku
     *
     * @param string $sku
     * @return int|null
     */
    protected function getProductIdBySku($sku)
    {
        if (empty($this->productIds)) {
            foreach ($this->productCollection as $product) {
                $this->productIds[$product->getSku()] = $product->getId();
            }
        }
        if (isset($this->productIds[$sku])) {
            return $this->productIds[$sku];
        }
        return null;
    }

    /**
     * @param array $row
     * @return \Magento\Review\Model\Review
     */
    protected function prepareReview($row)
    {
        /** @var $review \Magento\Review\Model\Review */
        $review = $this->reviewFactory->create();
        $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();
        $review->setEntityId(
            $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
        )->setEntityPkValue(
            $this->getProductIdBySku($row['sku'])
        )->setNickname(
            $row['reviewer']
        )->setTitle(
            $row['title']
        )->setDetail(
            $row['review']
        )->setStatusId(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->setStoreId(
            $storeId
        )->setStores(
            [$storeId]
        );
        return $review;
    }

    /**
     * @param string $rating
     * @return array
     */
    protected function getRating($rating)
    {
        $ratingCollection = $this->ratingFactory->create()->getResourceCollection();
        if (!$this->ratings[$rating]) {
            $this->ratings[$rating] = $ratingCollection->addFieldToFilter('rating_code', $rating)->getFirstItem();
        }
        return $this->ratings[$rating];
    }

    /**
     * @param \Magento\Review\Model\Review $review
     * @param array $row
     * @return void
     */
    protected function setReviewRating(\Magento\Review\Model\Review $review, $row)
    {
        $rating = $this->getRating($row['rating_code']);
        foreach ($rating->getOptions() as $option) {
            $optionId = $option->getOptionId();
            if (($option->getValue() == $row['rating_value']) && !empty($optionId)) {
                $rating->setReviewId($review->getId())->addOptionVote(
                    $optionId,
                    $this->getProductIdBySku($row['sku'])
                );
            }
        }
        $review->aggregate();
    }

    /**
     * @param string $ratingCode
     * @param array $stores
     * @return void
     */
    protected function createRating($ratingCode, $stores)
    {
        $stores[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $rating = $this->getRating($ratingCode);
        if (!$rating->getData()) {
            $rating->setRatingCode(
                $ratingCode
            )->setStores(
                $stores
            )->setIsActive(
                '1'
            )->setEntityId(
                $this->getRatingEntityId()
            )->save();

            /**Create rating options*/
            $options = [
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
            ];
            foreach ($options as $key => $optionCode) {
                $optionModel = $this->ratingOptionsFactory->create();
                $optionModel->setCode(
                    $optionCode
                )->setValue(
                    $key
                )->setRatingId(
                    $rating->getId()
                )->setPosition(
                    $key
                )->save();
            }
        }
    }

    /**
     * @param string $customerEmail
     * @return int|null
     */
    protected function getCustomerIdByEmail($customerEmail)
    {
        $customerData = $this->customerRepository->get($customerEmail);
        if ($customerData) {
            return $customerData->getId();
        }
        return null;
    }

    /**
     * @return int
     */
    protected function getRatingEntityId()
    {
        if (!$this->ratingProductEntityId) {
            $rating = $this->ratingFactory->create();
            $this->ratingProductEntityId = $rating->getEntityIdByCode(
                \Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE
            );
        }
        return $this->ratingProductEntityId;
    }

    /**
     * @return int
     */
    protected function getReviewEntityId()
    {
        if (!$this->reviewProductEntityId) {
            /** @var $review \Magento\Review\Model\Review */
            $review = $this->reviewFactory->create();
            $this->reviewProductEntityId = $review->getEntityIdByCode(
                \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE
            );
        }
        return $this->reviewProductEntityId;
    }
}
