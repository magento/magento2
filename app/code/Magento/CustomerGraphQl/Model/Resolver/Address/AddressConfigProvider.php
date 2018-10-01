<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Address;

use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Eav\Model\Config;

/**
 * Customers Address, used for GraphQL request processing.
 */
class AddressConfigProvider
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var array
     */
    private $addressAttributes;

    /**
     * @param Config $eavConfig
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Config $eavConfig,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->eavConfig = $eavConfig;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressAttributes = $this->eavConfig->getEntityAttributes(
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS
        );
    }

    /**
     * Add $addressInput array information to a $address object
     *
     * @param AddressInterface $address
     * @param array $addressInput
     * @return AddressInterface
     */
    public function fillAddress(AddressInterface $address, array $addressInput) : AddressInterface
    {
        $this->dataObjectHelper->populateWithArray(
            $address,
            $addressInput,
            AddressInterface::class
        );
        return $address;
    }

    /**
     * Get address field configuration
     *
     * @return array
     */
    public function getAddressAttributes() : array
    {
        return $this->addressAttributes;
    }
}
