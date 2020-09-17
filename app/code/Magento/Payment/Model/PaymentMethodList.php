<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\Data\PaymentMethodInterfaceFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Helper\Data;
use UnexpectedValueException;

class PaymentMethodList implements PaymentMethodListInterface
{
    /**
     * @var PaymentMethodInterfaceFactory
     */
    private $methodFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param PaymentMethodInterfaceFactory $methodFactory
     * @param Data $helper
     */
    public function __construct(
        PaymentMethodInterfaceFactory $methodFactory,
        Data $helper
    ) {
        $this->methodFactory = $methodFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getList($storeId)
    {
        $methodsCodes = array_keys($this->helper->getPaymentMethods());
        $methodsInstances = array_map(
            function ($code) {
                try {
                    return $this->helper->getMethodInstance($code);
                } catch (UnexpectedValueException $e) {
                    return null;
                }
            },
            $methodsCodes
        );

        $methodsInstances = array_filter($methodsInstances, function ($method) {
            return $method && !($method instanceof \Magento\Payment\Model\Method\Substitution);
        });

        uasort(
            $methodsInstances,
            function (MethodInterface $a, MethodInterface $b) use ($storeId) {
                return (int)$a->getConfigData('sort_order', $storeId) - (int)$b->getConfigData('sort_order', $storeId);
            }
        );

        $methodList = array_map(
            function (MethodInterface $methodInstance) use ($storeId) {

                return $this->methodFactory->create([
                    'code' => (string)$methodInstance->getCode(),
                    'title' => (string)$methodInstance->getTitle(),
                    'storeId' => (int)$storeId,
                    'isActive' => (bool)$methodInstance->isActive($storeId)
                ]);
            },
            $methodsInstances
        );

        return array_values($methodList);
    }

    /**
     * @inheritDoc
     */
    public function getActiveList($storeId)
    {
        $methodList = array_filter(
            $this->getList($storeId),
            function (PaymentMethodInterface $method) {
                return $method->getIsActive();
            }
        );

        return array_values($methodList);
    }
}
