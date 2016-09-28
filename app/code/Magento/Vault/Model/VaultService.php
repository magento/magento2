<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;

/**
 * Implementation of Vault Management
 */
class VaultService implements VaultManagementInterface
{
    /**
     * @var InstanceFactory
     */
    private $instanceFactory;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * Vault constructor.
     * @param PaymentMethodListInterface $paymentMethodList
     * @param InstanceFactory $instanceFactory
     */
    public function __construct(PaymentMethodListInterface $paymentMethodList, InstanceFactory $instanceFactory)
    {
        $this->instanceFactory = $instanceFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * @inheritdoc
     */
    public function getActivePaymentList($storeId)
    {
        $paymentMethods = array_map(
            function (PaymentMethodInterface $paymentMethod) {
                return $this->instanceFactory->create($paymentMethod);
            },
            $this->paymentMethodList->getActiveList($storeId)
        );

        $availableMethods = array_filter(
            $paymentMethods,
            function (MethodInterface $methodInstance) {
                return $methodInstance instanceof VaultPaymentInterface;
            }
        );

        return $availableMethods;
    }
}
