<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Shipping\Model\Config;

/**
 * Class CarrierRequestDataHydrator
 */
class CarrierRequestDataHydrator
{
    /**
     * @var SourceCarrierLinkInterface
     */
    private $carrierLinkFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * CarrierRequestDataHydrator constructor
     *
     * @param SourceCarrierLinkInterfaceFactory $carrierLinkFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Config $shippingConfig
     */
    public function __construct(
        SourceCarrierLinkInterfaceFactory $carrierLinkFactory,
        DataObjectHelper $dataObjectHelper,
        Config $shippingConfig
    ) {
        $this->carrierLinkFactory = $carrierLinkFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @param SourceInterface $source
     * @param array $requestData
     * @return SourceInterface
     * @throws InputException
     */
    public function hydrate(SourceInterface $source, array $requestData)
    {
        $useDefaultCarrierConfig = isset($requestData[SourceInterface::USE_DEFAULT_CARRIER_CONFIG])
            && true === (bool)$requestData[SourceInterface::USE_DEFAULT_CARRIER_CONFIG];

        $carrierLinks = [];
        if (false === $useDefaultCarrierConfig
            && isset($requestData['carrier_codes'])
            && is_array($requestData['carrier_codes'])
        ) {
            $this->checkCarrierCodes($requestData['carrier_codes']);
            $carrierLinks = $this->createCarrierLinks($requestData['carrier_codes']);
        }

        $source->setUseDefaultCarrierConfig($useDefaultCarrierConfig);
        $source->setCarrierLinks($carrierLinks);
        return $source;
    }

    /**
     * @param array $carrierCodes
     * @throws InputException
     */
    private function checkCarrierCodes(array $carrierCodes)
    {
        $availableCarriers = $this->shippingConfig->getAllCarriers();

        if (count(array_intersect_key(array_flip($carrierCodes), $availableCarriers)) !== count($carrierCodes)) {
            throw new InputException(__('Wrong carrier codes data'));
        }
    }

    /**
     * @param array $carrierCodes
     * @return SourceCarrierLinkInterface[]
     */
    private function createCarrierLinks(array $carrierCodes)
    {
        $carrierLinks = [];
        foreach ($carrierCodes as $carrierCode) {
            /** @var SourceCarrierLinkInterface $carrierLink */
            $carrierLink = $this->carrierLinkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $carrierLink,
                [
                    SourceCarrierLinkInterface::CARRIER_CODE => $carrierCode,
                ],
                SourceCarrierLinkInterface::class
            );
            $carrierLinks[] = $carrierLink;
        }
        return $carrierLinks;
    }
}
