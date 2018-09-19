<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin\Authorization;

use Magento\Framework\App\RequestInterface;

/**
 * This plugin allows only AJAX requests when customers access web APIs.
 */
class CustomerSessionUserContext
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Initialize dependencies.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Allow only AJAX requests when customers access web APIs.
     *
     * @param \Magento\Customer\Model\Authorization\CustomerSessionUserContext $userContext
     * @param int|null $result
     * @return int|null
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUserId(
        \Magento\Customer\Model\Authorization\CustomerSessionUserContext $userContext,
        $result
    ) {
        return $this->request->isXmlHttpRequest() ? $result : null;
    }
}
