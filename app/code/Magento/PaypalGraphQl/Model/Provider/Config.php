<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Provider;

use Magento\Framework\ObjectManagerInterface;
use Magento\Paypal\Model\AbstractConfig;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Provides correct Config instance for payment method
 */
class Config
{
    /**
     * @var array
     */
    private $configTypes;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $configTypes
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $configTypes
    ) {
        $this->objectManager = $objectManager;
        $this->configTypes = $configTypes;
    }

    /**
     * Get Config model by payment method code
     *
     * @param string $paymentMethod
     * @return AbstractConfig
     * @throws GraphQlInputException
     */
    public function getConfig(string $paymentMethod): AbstractConfig
    {
        //validate code string
        if (empty($this->configTypes[$paymentMethod]) || !class_exists($this->configTypes[$paymentMethod])) {
            throw new GraphQlInputException(__('The requested Payment Method is not available.'));
        }

        /** @var AbstractConfig $config */
        $config = $this->objectManager->get($this->configTypes[$paymentMethod]);
        $config->setMethod($paymentMethod);

        if (!$config->isMethodAvailable($paymentMethod)) {
            throw new GraphQlInputException(__('The requested Payment Method is not available.'));
        }

        return $config;
    }
}
