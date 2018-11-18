<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Api;

use Magento\ReviewApi\Api\Data\ReviewInterface;

/**
 * @api
 */
interface ReviewOperationResponseInterface
{
    /**
     * Add successful review
     *
     * @param ReviewInterface[] $reviews
     * @return ReviewOperationResponseInterface
     */
    public function addSuccessfulReviews(array $reviews): ReviewOperationResponseInterface;

    /**
     * Add retryable review
     *
     * @param ReviewInterface[] $reviews
     * @return ReviewOperationResponseInterface
     */
    public function addRetryableReviews(array $reviews): ReviewOperationResponseInterface;

    /**
     * Add failed review
     *
     * @param ReviewInterface[] $reviews
     * @return ReviewOperationResponseInterface
     */
    public function addFailedReviews(array $reviews): ReviewOperationResponseInterface;

    /**
     * Add error
     *
     * @param string $error
     * @return ReviewOperationResponseInterface
     */
    public function addError($error): ReviewOperationResponseInterface;

    /**
     * Get successful saved reviews
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    public function getSuccessfulReviews(): array;

    /**
     * Get retryable reviews
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    public function getRetryableReviews(): array;

    /**
     * Get failed reviews
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    public function getFailedReviews(): array;

    /**
     * Get errors
     *
     * @return string[]
     */
    public function getErrors(): array;
}
