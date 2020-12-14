<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Updates quote store id.
 */
class UpdateQuoteStoreId
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param QuoteRepository $quoteRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Update store id in requested quote by store id from request.
     *
     * @param Quote $subject
     * @param null $result
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadByIdWithoutStore(
        Quote $subject,
        $result
    ) {
        $quoteStoreId = (int) $subject->getStoreId();
        $storeId = $this->storeManager->getStore()
            ->getId() ?: $this->storeManager->getDefaultStoreView()
                ->getId();
        if ($storeId !== $quoteStoreId) {
            $subject->setStoreId($storeId);
        }
    }
}
