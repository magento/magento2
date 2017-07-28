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
 * @since 2.2.0
 */
class PaymentMethodList implements VaultPaymentMethodListInterface
{
    /**
     * @var InstanceFactory
     * @since 2.2.0
     */
    private $instanceFactory;

    /**
     * @var PaymentMethodListInterface
     * @since 2.2.0
     */
    private $paymentMethodList;

    /**
     * PaymentMethodList constructor.
     * @param PaymentMethodListInterface $paymentMethodList
     * @param InstanceFactory $instanceFactory
     * @since 2.2.0
     */
    public function __construct(PaymentMethodListInterface $paymentMethodList, InstanceFactory $instanceFactory)
    {
        $this->instanceFactory = $instanceFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getList($storeId)
    {
        return $this->filterList($this->paymentMethodList->getList($storeId));
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getActiveList($storeId)
    {
        return $this->filterList($this->paymentMethodList->getActiveList($storeId));
    }

    /**
     * Filter vault methods from payments
     * @param PaymentMethodInterface[] $list
     * @return VaultPaymentInterface[]
     * @since 2.2.0
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
