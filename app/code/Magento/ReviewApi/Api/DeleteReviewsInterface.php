<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Api;

/**
 * @api
 */
interface DeleteReviewsInterface
{
    /**
     * Delete reviews
     *
     * @param \Magento\ReviewApi\Api\Data\ReviewInterface[] $reviews
     * @return \Magento\ReviewApi\Api\ReviewOperationResponseInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute(array $reviews): ReviewOperationResponseInterface;
}
