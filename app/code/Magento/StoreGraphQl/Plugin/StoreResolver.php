<?php

namespace Magento\StoreGraphQl\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Resolver\Store;

/**
 * Ensure the store resolver gets the correct scope based on the GraphQl header
 */
class StoreResolver
{
    /**
     * @var RequestInterface|HttpRequest
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * If no scope is provided and there is a Store header, ensure the correct store code is used
     *
     * @param Store $subject
     * @param null $scopeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetScope(Store $subject, $scopeId = null)
    {
        $storeCode = $this->request->getHeader('Store');

        if ($scopeId === null && $storeCode) {
            return [$storeCode];
        }
    }
}
