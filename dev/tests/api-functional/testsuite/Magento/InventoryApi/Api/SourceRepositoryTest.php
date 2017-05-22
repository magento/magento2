<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;

class SourceRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    const RESOURCE_PATH = '/V1/inventory/source/';

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory
     */
    private $sourceCarrierLinkFactory;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->sourceFactory = Bootstrap::getObjectManager()
            ->create(\Magento\InventoryApi\Api\Data\SourceInterfaceFactory::class);
        $this->countryInformationAcquirer = Bootstrap::getObjectManager()
            ->create(CountryInformationAcquirerInterface::class);
        $this->sourceCarrierLinkFactory = Bootstrap::getObjectManager()
            ->create(\Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory::class);
    }

    /**
     * Create new Inventory Source using Web API and verify it's integrity.
     */
    public function testCreateSource()
    {
        $country = $this->countryInformationAcquirer->getCountryInfo('DE');

        $name = 'Source name';
        $description = 'Some description for source';
        $city = 'Exampletown';
        $street = 'Some Street 455';
        $postcode = 54321;
        $contactName = 'Contact Name';
        $email = 'example.guy@test.com';
        $fax = '0120002020033';
        $phone = '0120002020044';
        $latitude = 51.343479;
        $longitude = 12.387772;
        $isActive = true;
        $priority = 40;

        $carrierCode1 = 'CAR-1';
        $carrierCode2 = 'CAR-2';

        /** @var SourceCarrierLinkInterface $carrierLink1 */
        $carrierLink1 = $this->sourceCarrierLinkFactory->create();
        $carrierLink1->setPosition(1)
            ->setCarrierCode($carrierCode1);

        /** @var SourceCarrierLinkInterface $carrierLink2*/
        $carrierLink2 = $this->sourceCarrierLinkFactory->create();
        $carrierLink2->setPosition(2)
            ->setCarrierCode($carrierCode2);

        /** @var  \Magento\InventoryApi\Api\Data\SourceInterface $sourceDataObject */
        $sourceDataObject = $this->sourceFactory->create();
        $sourceDataObject->setName($name)
            ->setDescription($description)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setContactName($contactName)
            ->setCountryId($country->getId())
            ->setEmail($email)
            ->setFax($fax)
            ->setPhone($phone)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setIsActive($isActive)
            ->setPriority($priority)
            ->setStreet($street)
            ->setCarrierLinks([
                $carrierLink1,
                $carrierLink2
            ]);

        $regions = $country->getAvailableRegions();
        if ($regions) {
            $region = $regions[0];
            $sourceDataObject->setRegion($region->getName());
            $sourceDataObject->setRegionId($region->getId());
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];


        $requestData = [
            'source' => [
                SourceInterface::CITY => $sourceDataObject->getCity(),
                SourceInterface::CONTACT_NAME => $sourceDataObject->getContactName(),
                SourceInterface::COUNTRY_ID => $sourceDataObject->getCountryId(),
                SourceInterface::DESCRIPTION => $sourceDataObject->getDescription(),
                SourceInterface::NAME => $sourceDataObject->getName(),
                SourceInterface::EMAIL => $sourceDataObject->getEmail(),
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        var_dump($result);
    }

    /**
     * Load already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testGetSource()
    {
        //TODO: Implement testGetSource
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Load and verify integrity of a list of already existing Inventory Sources filtered by Search Criteria.
     */
    public function testGetSourcesList()
    {
        //TODO: Implement testGetSourcesList
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Update already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testUpdateSource()
    {
        //TODO: Implement testUpdateSource
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Update already existing Inventory Source, removing carrier links, using Web API and verify it's integrity.
     */
    public function testUpdateSourceWithoutCarriers()
    {
        //TODO: Implement testUpdateSourceWithoutCarriers
        $this->fail(__METHOD__ . " is not implemented yet.");
    }
}
