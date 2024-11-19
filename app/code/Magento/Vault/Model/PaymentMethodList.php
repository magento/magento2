<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Api\PaymentMethodListInterface as VaultPaymentMethodListInterface;

/**
 * Contains methods to retrieve configured vault payments
 */
class PaymentMethodList implements VaultPaymentMethodListInterface
{
    /**
     * PaymentMethodList constructor.
     * @param PaymentMethodListInterface $paymentMethodList
     * @param InstanceFactory $instanceFactory
     */
    public function __construct(
        private readonly PaymentMethodListInterface $paymentMethodList,
        private readonly InstanceFactory $instanceFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getList($storeId)
    {
        return $this->filterList($this->paymentMethodList->getList($storeId));
    }

    /**
     * @inheritdoc
     */
    public function getActiveList($storeId)
    {
        return $this->filterList($this->paymentMethodList->getActiveList($storeId));
    }

    /**
     * Filter vault methods from payments
     * @param PaymentMethodInterface[] $list
     * @return VaultPaymentInterface[]
     */
    private function filterList(array $list)
    {
        $paymentMethods = array_map(
            function (PaymentMethodInterface $paymentMethod) {
                return $this->instanceFactory->create($paymentMethod);
            },
            $list
        );

        $availableMethods = array_filter(
            $paymentMethods,
            function (MethodInterface $methodInstance) {
                return $methodInstance instanceof VaultPaymentInterface;
            }
        );
        return array_values($availableMethods);
    }
}
