<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config;

/**
 * Class CarrierSource
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
     * CarrierSource constructor
     *
     * @param Config $shippingConfig
     */
    public function __construct(Config $shippingConfig)
    {
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->sourceData) {
            $carriers = $this->shippingConfig->getActiveCarriers();
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
