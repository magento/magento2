<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Quote\Model\Quote\Address;

/**
 * Provides additional data for multishipping checkout billing step
 *
 * @see \Magento\Multishipping\view\frontend\templates\checkout\billing.phtml
 */
class Billing implements ArgumentInterface
{
    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param AddressConfig $addressConfig
     * @param CompositeConfigProvider $configProvider
     * @param Serializer $serializer
     */
    public function __construct(
        AddressConfig $addressConfig,
        CompositeConfigProvider $configProvider,
        Serializer $serializer
    ) {
        $this->addressConfig = $addressConfig;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }

    /**
     * Get address formatted as html string.
     *
     * @param Address $address
     * @return string
     */
    public function getAddressHtml(Address $address): string
    {
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();

        return $renderer->renderArray($address->getData());
    }

    /**
     * Returns serialized checkout config.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSerializedCheckoutConfigs(): string
    {
        return $this->serializer->serialize($this->configProvider->getConfig());
    }
}
