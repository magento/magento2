<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Request;

use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * @inheritdoc
 */
class AddressRequest implements AddressRequestInterface
{
    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var string
     */
    private $streetAddress;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $city;

    /**
     * ItemRequestAddress constructor.
     *
     * @param string $country
     * @param string $postcode
     * @param string $streetAddress
     * @param string $region
     * @param string $city
     */
    public function __construct(
        string $country,
        string $postcode,
        string $streetAddress,
        string $region,
        string $city
    ) {
        $this->country = $country;
        $this->postcode = $postcode;
        $this->streetAddress = $streetAddress;
        $this->region = $region;
        $this->city = $city;
    }

    /**
     * @inheritdoc
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @inheritdoc
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @inheritdoc
     */
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    /**
     * @inheritdoc
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @inheritdoc
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Get address as string
     *
     * @return string
     */
    public function getAsString(): string
    {
        return implode(' ', [
            $this->getStreetAddress(),
            $this->getPostcode(),
            $this->getCity(),
            $this->getRegion(),
            $this->getCountry()
        ]);
    }
}
