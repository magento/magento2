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
use Magento\Framework\Webapi\Request;

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
        $store = $this->getStore($this->request->getPathInfo());
        if ($store) {
            $result->setStoreId($store->getId());
        }

        return $result;
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
        $storeCode = current($pathParts);
        $stores = $this->storeManager->getStores(false, true);

        return $stores[$storeCode] ?? null;
    }
}
