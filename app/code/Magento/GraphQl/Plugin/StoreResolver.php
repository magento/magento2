<?php

namespace Magento\GraphQl\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Load translations for GraphQL requests
 */
class StoreResolver
{
    /**
     * @var RequestInterface|HttpRequest
     */
    private $request;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param RequestInterface $request
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        RequestInterface $request,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->request = $request;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Use the 'Store' header to determine the current store
     *
     * @param \Magento\Store\Model\StoreResolver $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundGetCurrentStoreId(\Magento\Store\Model\StoreResolver $subject, callable $proceed)
    {
        $storeCode = $this->request->getHeader('Store');

        if ($storeCode) {
            try {
                $store = $this->getRequestedStoreByCode($storeCode);

                if ($store) {
                    return $store;
                }
            } catch (NoSuchEntityException $e) {
                return $proceed();
            }
        }

        return $proceed();
    }

    /**
     * Retrieve active store by code
     *
     * @param string $storeCode
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getRequestedStoreByCode($storeCode) : StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            throw new NoSuchEntityException(__('Requested store is inactive'));
        }

        return $store;
    }
}
