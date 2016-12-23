<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Signifyd\Model\OrderSessionId;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const SIGNIFYD_CODE = 'signifyd';

    /**
     * @var OrderSessionId
     */
    private $orderSessionId;

    /**
     * @param OrderSessionId $orderSessionId
     */
    public function __construct(OrderSessionId $orderSessionId)
    {
        $this->orderSessionId = $orderSessionId;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'fraud_protection' => [
                self::SIGNIFYD_CODE => [
                    'orderSessionId' => $this->orderSessionId->generate()
                ]
            ]
        ];
    }
}
