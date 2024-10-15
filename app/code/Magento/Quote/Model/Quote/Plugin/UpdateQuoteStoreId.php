<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as Request;

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
     * @var Request
     */
    private $request;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Request $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Request $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * Update store id in requested quote by store id from guest's request.
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadByIdWithoutStore(Quote $subject, Quote $result): Quote
    {
        return $this->updateStoreId($result);
    }

    /**
     * Update store id in requested quote by store id from registered customer's request.
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadByCustomer(Quote $subject, Quote $result): Quote
    {
        return $this->updateStoreId($result);
    }

    /**
     * Returns store based on web-api request path.
     *
     * @param string $requestPath
     * @return StoreInterface|null
     */
    private function getStore(string $requestPath): ?StoreInterface
    {
        $pathParts = explode('/', trim($requestPath, '/'));
        array_shift($pathParts);
        $storeCode = current($pathParts);
        $stores = $this->storeManager->getStores(false, true);

        return $stores[$storeCode] ?? null;
    }

    /**
     * Update store id in requested quote by store id from request.
     *
     * @param Quote $quote
     * @return Quote
     */
    private function updateStoreId(Quote $quote): Quote
    {
        $store = $this->getStore($this->request->getPathInfo());
        if ($store) {
            $quote->setStoreId($store->getId());
        }

        return $quote;
    }
}
