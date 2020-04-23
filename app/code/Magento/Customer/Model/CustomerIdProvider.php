<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\App\RequestInterface;

/**
 * Provides customer id from request.
 */
class CustomerIdProvider
{
    /**
     * @var RequestInterface
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
     * Get customer id from request.
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->request->getParam('id');
    }
}
