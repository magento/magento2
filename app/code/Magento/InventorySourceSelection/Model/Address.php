<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model;

use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;

/**
 * @inheritdoc
 */
class Address implements AddressInterface
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
    private $street;

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
     * @param string $street
     * @param string $region
     * @param string $city
     */
    public function __construct(
        string $country,
        string $postcode,
        string $street,
        string $region,
        string $city
    ) {
        $this->country = $country;
        $this->postcode = $postcode;
        $this->street = $street;
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
    public function getStreet(): string
    {
        return $this->street;
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
}
