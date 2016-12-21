<?php
namespace Magento\Review\Api;

/**
 * Interface ReviewInterface
 * @api
 */
interface ReviewInterface
{
    /**
     * Return Added review item.
     *
     * @param int $productId
     * @return []
     *
     */
    public function getReviewsList($productId);

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
     *
     */
    public function writeReviews(
        $productId,
        $nickname,
        $title,
        $detail,
        $ratingValue,
        $customerId = null,
        $storeId = 1
    );
}
