<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Product\View;

/**
 * Detailed Product Reviews
 *
 * @api
 * @since 2.0.0
 */
class ListView extends \Magento\Review\Block\Product\View
{
    /**
     * Unused class property
     * @var false
     * @since 2.0.0
     */
    protected $_forceHasOptions = false;

    /**
     * Get product id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * Prepare product review list toolbar
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $toolbar = $this->getLayout()->getBlock('product_review_list.toolbar');
        if ($toolbar) {
            $toolbar->setCollection($this->getReviewsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }

    /**
     * Add rate votes
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->getReviewsCollection()->load()->addRateVotes();
        return parent::_beforeToHtml();
    }

    /**
     * Return review url
     *
     * @param int $id
     * @return string
     * @since 2.0.0
     */
    public function getReviewUrl($id)
    {
        return $this->getUrl('*/*/view', ['id' => $id]);
    }
}
