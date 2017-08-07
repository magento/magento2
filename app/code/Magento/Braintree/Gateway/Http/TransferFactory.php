<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class \Magento\Braintree\Gateway\Http\TransferFactory
 *
 * @since 2.1.0
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     * @since 2.1.0
     */
    private $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     * @since 2.1.0
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     * @since 2.1.0
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
