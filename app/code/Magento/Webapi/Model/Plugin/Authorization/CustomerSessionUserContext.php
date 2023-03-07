<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin\Authorization;

use Magento\Customer\Model\Authorization\CustomerSessionUserContext as AuthorizationCustomerSessionUserContext;
use Magento\Framework\App\RequestInterface;

/**
 * This plugin allows only AJAX requests when customers access web APIs.
 */
class CustomerSessionUserContext
{
    /**
     * Initialize dependencies.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Allow only AJAX requests when customers access web APIs.
     *
     * @param AuthorizationCustomerSessionUserContext $userContext
     * @param int|null $result
     * @return int|null
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUserId(
        AuthorizationCustomerSessionUserContext $userContext,
        $result
    ) {
        return $this->request->isXmlHttpRequest() ? $result : null;
    }
}
