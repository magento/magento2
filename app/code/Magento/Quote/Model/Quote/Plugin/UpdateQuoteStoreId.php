<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreCodeInRequestPathInterface;
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
     * @var StoreCodeInRequestPathInterface
     */
    private $storeCodeInRequestPath;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StoreCodeInRequestPathInterface $storeCoeInRequestPath
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StoreCodeInRequestPathInterface $storeCodeInRequestPath
    ) {
        $this->storeManager = $storeManager;
        $this->storeCodeInRequestPath = $storeCodeInRequestPath;
    }

    /**
     * Update store id in requested quote by store id from request.
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterLoadByIdWithoutStore(Quote $subject, Quote $result): Quote
    {
        if ($this->storeCodeInRequestPath->hasStoreCodeInRequestPath()) {
            $storeId = $this->storeManager->getStore()->getId();
            if ((int)$storeId !== $result->getStoreId()) {
                $result->setStoreId($storeId);
            }
        }

        return $result;
    }

    /**
     * Update store id in requested quote by store id from request for registered customer.
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterLoadByCustomer(Quote $subject, Quote $result): Quote
    {
        if ($this->storeCodeInRequestPath->hasStoreCodeInRequestPath()) {
            $storeId = $this->storeManager->getStore()->getId();
            if ((int)$storeId !== $result->getStoreId()) {
                $result->setStoreId($storeId);
            }
        }

        return $result;
    }
}
