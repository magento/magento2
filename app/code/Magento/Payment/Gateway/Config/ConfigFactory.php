<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\ConfigFactoryInterface;

/**
 * Class \Magento\Payment\Gateway\Config\ConfigFactory
 *
 * @since 2.1.0
 */
class ConfigFactory implements ConfigFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $om;

    /**
     * ConfigFactory constructor.
     * @param ObjectManagerInterface $om
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $om
    ) {
        $this->om = $om;
    }

    /**
     * @param string|null $paymentCode
     * @param string|null $pathPattern
     * @return mixed
     * @since 2.1.0
     */
    public function create($paymentCode = null, $pathPattern = null)
    {
        $arguments = [
            'methodCode' => $paymentCode
        ];

        if ($pathPattern !== null) {
            $arguments['pathPattern'] = $pathPattern;
        }

        return $this->om->create(Config::class, $arguments);
    }
}
