<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Updates quote store id.
 */
class UpdateQuoteStoreId
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Update store id in requested quote by store id from request.
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadByIdWithoutStore(Quote $subject, Quote $result): Quote
    {
        $storeId = $this->storeManager->getStore()
            ->getId() ?: $this->storeManager->getDefaultStoreView()
                ->getId();
        $result->setStoreId($storeId);

        return $result;
    }
}
