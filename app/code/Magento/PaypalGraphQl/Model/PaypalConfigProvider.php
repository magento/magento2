<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Paypal\Model\AbstractConfig;
use Magento\Paypal\Model\Express\Checkout;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Quote\Api\Data\CartInterface;

class PaypalConfigProvider
{
    /**
     * @var array
     */
    private $configurations;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var string[]
     */
    private $bmlCodeList;

    /**
     * @param ObjectManager $objectManager
     * @param CheckoutFactory $checkoutFactory
     * @param array $configurations
     */
    public function __construct(
        ObjectManager $objectManager,
        CheckoutFactory $checkoutFactory,
        array $configurations,
        array $bmlCodeList = []
    ) {
        $this->objectManager = $objectManager;
        $this->checkoutFactory = $checkoutFactory;
        $this->configurations = $configurations;
        $this->bmlCodeList = $bmlCodeList;
    }

    /**
     * Get Config model by payment method code
     *
     * @param string $code
     * @return \Magento\Paypal\Model\AbstractConfig
     * @throws GraphQlInputException
     */
    public function getConfig(string $code): \Magento\Paypal\Model\AbstractConfig
    {
        //validate code string
        if (empty($this->configurations[$code])
            || empty($this->configurations[$code]['configType'])
            || !class_exists($this->configurations[$code]['configType'])
        ) {
            throw new GraphQlInputException(__("TODO Invalid payment code"));
        }

        /** @var AbstractConfig $configObject */
        $configObject = $this->objectManager->get($this->configurations[$code]['configType']);
        $configObject->setMethod($this->configurations[$code]['configMethod']);

        if (!$configObject->isMethodAvailable($this->configurations[$code]['configMethod'])) {
            throw new GraphQlInputException(__("TODO Payment method not available"));
        }

        return $configObject;
    }

    /**
     * Get Checkout model by payment method code
     *
     * @param string $code
     * @param CartInterface $cart
     * @return Checkout
     * @throws GraphQlInputException
     */
    public function getCheckout(string $code, CartInterface $cart): Checkout
    {
        $config = $this->getConfig($code);

        try {
            $checkoutObject = $this->checkoutFactory->create(
                $this->configurations[$code]['checkoutType'],
                [
                    'params' => [
                        'quote' => $cart,
                        'config' => $config,
                    ],
                ]
            );

            if (in_array($code, $this->bmlCodeList)) {
                $checkoutObject->setIsBml(true);
            }
        } catch (\Exception $e) {
            throw new GraphQlInputException(__("Express Checkout class not found"));
        }

        return $checkoutObject;
    }
}
