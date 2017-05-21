<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;

class SourceRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    const RESOURCE_PATH = '/V1/inventory/source/';

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var SourceCarrierLinkInterfaceFactory
     */
    private $sourceCarrierLinkFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->sourceFactory = Bootstrap::getObjectManager()
            ->create(SourceInterfaceFactory::class);

        $this->countryInformationAcquirer = Bootstrap::getObjectManager()
            ->create(CountryInformationAcquirerInterface::class);

        $this->sourceCarrierLinkFactory = Bootstrap::getObjectManager()
            ->create(SourceCarrierLinkInterfaceFactory::class);

        $this->sourceRepository = Bootstrap::getObjectManager()
            ->create(SourceRepositoryInterface::class);

        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        $this->sortOrderBuilder = Bootstrap::getObjectManager()
            ->create(SortOrderBuilder::class);
    }

    /**
     * Create new Inventory Source using Web API and verify it's integrity.
     */
    public function testCreateSource()
    {
        $country = $this->countryInformationAcquirer->getCountryInfo('DE');
        $regions = $country->getAvailableRegions();
        $region = $regions[0];

        $name = 'Source name';
        $description = 'Some description for source';
        $city = 'Exampletown';
        $street = 'Some Street 455';
        $postcode = '54321';
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

        /** @var SourceCarrierLinkInterface $expectedCarrierLink1 */
        $expectedCarrierLink1 = $this->sourceCarrierLinkFactory->create();
        $expectedCarrierLink1->setPosition(1)
            ->setCarrierCode($carrierCode1);

        /** @var SourceCarrierLinkInterface $expectedCarrierLink2 */
        $expectedCarrierLink2 = $this->sourceCarrierLinkFactory->create();
        $expectedCarrierLink2->setPosition(2)
            ->setCarrierCode($carrierCode2);

        /** @var  \Magento\InventoryApi\Api\Data\SourceInterface $expectedSource */
        $expectedSource = $this->sourceFactory->create();
        $expectedSource->setName($name)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setContactName($contactName)
            ->setCountryId($country->getId())
            ->setDescription($description)
            ->setEmail($email)
            ->setStreet($street)
            ->setFax($fax)
            ->setPhone($phone)
            ->setRegion($region->getName())
            ->setRegionId($region->getId())
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setIsActive($isActive)
            ->setPriority($priority)
            ->setCarrierLinks([
                $expectedCarrierLink1,
                $expectedCarrierLink2
            ]);

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
            'source' => $this->formSourceToArray($expectedSource)
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($result);

        $createdSource = $this->sourceRepository->get($result);
        $this->assertSame($this->formSourceToArray($expectedSource), $this->formSourceToArray($createdSource));
    }

    /**
     * @param SourceInterface $source
     * @return array
     */
    private function formSourceToArray(SourceInterface $source)
    {
        $result = [
            SourceInterface::NAME => $source->getName(),
            SourceInterface::CITY => $source->getCity(),
            SourceInterface::POSTCODE => $source->getPostcode(),
            SourceInterface::CONTACT_NAME => $source->getContactName(),
            SourceInterface::COUNTRY_ID => $source->getCountryId(),
            SourceInterface::DESCRIPTION => $source->getDescription(),
            SourceInterface::EMAIL => $source->getEmail(),
            SourceInterface::STREET => $source->getStreet(),
            SourceInterface::FAX => $source->getFax(),
            SourceInterface::PHONE => $source->getPhone(),
            SourceInterface::REGION => $source->getRegion(),
            SourceInterface::REGION_ID => $source->getRegionId(),
            SourceInterface::LATITUDE => $source->getLatitude(),
            SourceInterface::LONGITUDE => $source->getLongitude(),
            SourceInterface::IS_ACTIVE => $source->getIsActive(),
            SourceInterface::PRIORITY => $source->getPriority(),
            SourceInterface::CARRIER_LINKS => []
        ];

        $carrierLinks = $source->getCarrierLinks();
        if ($carrierLinks) {
            foreach ($carrierLinks as $carrierLink) {
                $result[SourceInterface::CARRIER_LINKS][] = [
                    SourceCarrierLinkInterface::CARRIER_CODE => $carrierLink->getCarrierCode(),
                    SourceCarrierLinkInterface::POSITION => $carrierLink->getPosition(),
                ];
            }
        }

        return $result;
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
