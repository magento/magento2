<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;

/**
 * Can create a transfer object
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var array
     */
    private $payloadFilters;

    /**
     * @param TransferBuilder $transferBuilder
     * @param FilterInterface[] $payloadFilters
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        array $payloadFilters = []
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->payloadFilters = $payloadFilters;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        foreach ($this->payloadFilters as $filter) {
            $request = $filter->filter($request);
        }

        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
