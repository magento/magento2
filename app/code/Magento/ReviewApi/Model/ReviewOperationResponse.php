<?php

namespace Magento\ReviewApi\Model;

use Magento\ReviewApi\Api\ReviewOperationResponseInterface;

/**
 * Class ReviewOperationResponse
 */
class ReviewOperationResponse implements ReviewOperationResponseInterface
{
    /**
     * Successful reviews
     *
     * @var \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    private $successful = [];

    /**
     * Retryable reviews
     *
     * @var \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    private $retryable = [];

    /**
     * Failed reviews
     *
     * @var \Magento\ReviewApi\Api\Data\ReviewInterface[]
     */
    private $failed = [];

    /**
     * Errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * @inheritdoc
     */
    public function addSuccessfulReviews(array $reviews): ReviewOperationResponseInterface
    {
        $this->successful += $reviews;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addRetryableReviews(array $reviews): ReviewOperationResponseInterface
    {
        $this->retryable += $reviews;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFailedReviews(array $reviews): ReviewOperationResponseInterface
    {
        $this->failed += $reviews;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addError($error): ReviewOperationResponseInterface
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSuccessfulReviews(): array
    {
        return $this->successful;
    }

    /**
     * @inheritdoc
     */
    public function getRetryableReviews(): array
    {
        return $this->retryable;
    }

    /**
     * @inheritdoc
     */
    public function getFailedReviews(): array
    {
        return $this->failed;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
