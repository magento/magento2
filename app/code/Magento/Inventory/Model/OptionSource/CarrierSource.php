<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config;

/**
 * Provide option values for UI
 *
 * @api
 */
class CarrierSource implements OptionSourceInterface
{
    /**
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * Source data
     *
     * @var null|array
     */
    private $sourceData;

    /**
     * @param Config $shippingConfig
     */
    public function __construct(Config $shippingConfig)
    {
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (null === $this->sourceData) {
            $carriers = $this->shippingConfig->getAllCarriers();
            foreach ($carriers as $carrier) {
                $this->sourceData[] = [
                    'value' => $carrier->getCarrierCode(),
                    'label' => $carrier->getConfigData('title'),
                ];
            }
        }
        return $this->sourceData;
    }
}
