<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\Review\AppendSummaryDataToObject;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\ReviewSummaryFactory;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;

/**
 * Product Review Renderer
 */
class ReviewRenderer extends Template implements ReviewRendererInterface
{
    /**
     * Array of available template name
     *
     * @var array
     */
    protected $_availableTemplates = [
        self::FULL_VIEW => 'Magento_Review::helper/summary.phtml',
        self::SHORT_VIEW => 'Magento_Review::helper/summary_short.phtml',
    ];

    /**
     * @deprecated due to new class introduced
     * @see $this->>appendSummaryDataToObject
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @deprecated due to new class introduced
     * @see $this->>appendSummaryDataToObject
     * @var ReviewSummaryFactory
     */
    private $reviewSummaryFactory;

    /**
     * @var AppendSummaryDataToObject
     */
    private $appendSummaryDataToObject;

    /**
     * @param Context $context
     * @param ReviewFactory $reviewFactory
     * @param array $data
     * @param ReviewSummaryFactory|null $reviewSummaryFactory
     * @param AppendSummaryDataToObject|null $appendSummaryDataToObject
     */
    public function __construct(
        Context $context,
        ReviewFactory $reviewFactory,
        array $data = [],
        ?ReviewSummaryFactory $reviewSummaryFactory = null,
        ?AppendSummaryDataToObject $appendSummaryDataToObject = null
    ) {
        parent::__construct($context, $data);
        $this->_reviewFactory = $reviewFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory ??
            ObjectManager::getInstance()->get(ReviewSummaryFactory::class);
        $this->appendSummaryDataToObject = $appendSummaryDataToObject ??
            ObjectManager::getInstance()->get(AppendSummaryDataToObject::class);
    }

    /**
     * Review module availability
     *
     * @return string
     */
    public function isReviewEnabled(): string
    {
        return $this->_scopeConfig->getValue(
            PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get review summary html
     *
     * @param Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getReviewsSummaryHtml(
        Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if ($product->getRatingSummary() === null) {
            $this->appendSummaryDataToObject->execute(
                $product,
                (int)$this->_storeManager->getStore()->getId()
            );
        }

        if (null === $product->getRatingSummary() && !$displayIfNoReviews) {
            return '';
        }
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = self::DEFAULT_VIEW;
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        $this->setProduct($product);

        return $this->toHtml();
    }

    /**
     * Get ratings summary
     *
     * @return string
     */
    public function getRatingSummary()
    {
        return $this->getProduct()->getRatingSummary();
    }

    /**
     * Get count of reviews
     *
     * @return int
     */
    public function getReviewsCount()
    {
        return $this->getProduct()->getReviewsCount();
    }

    /**
     * Get review product list url
     *
     * @param bool $useDirectLink allows to use direct link for product reviews page
     *
     * @return string
     */
    public function getReviewsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }
}
