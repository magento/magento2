<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SourceRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    const RESOURCE_PATH = '/V1/inventory/source/';
    const TEST_PREFIX = 'SOURCE_APITEST_';

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
     * @var FilterBuilder
     */
    private $filterBuilder;

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

        $this->filterBuilder = Bootstrap::getObjectManager()
            ->create(FilterBuilder::class);

        $this->sortOrderBuilder = Bootstrap::getObjectManager()
            ->create(SortOrderBuilder::class);
    }

    /**
     * @param array $sourceData
     * @return array
     */
    private function getExpectedValues(array $sourceData)
    {
        $sourceData[SourceInterface::LATITUDE] = number_format(
            $sourceData[SourceInterface::LATITUDE],
            6
        );
        $sourceData[SourceInterface::LONGITUDE] = number_format(
            $sourceData[SourceInterface::LONGITUDE],
            6
        );

        return $sourceData;
    }

    /**
     * @param SourceInterface $source
     * @return array
     */
    private function getSourceDataArray(SourceInterface $source)
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
            SourceInterface::ENABLED => $source->isEnabled(),
            SourceInterface::PRIORITY => $source->getPriority(),
            SourceInterface::USE_DEFAULT_CARRIER_CONFIG => $source->isUseDefaultCarrierConfig(),
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
     * @param int $countCarrier
     * @param string $postcode
     * @return SourceInterface
     */
    private function createRandomSource($countCarrier = 2, $postcode = '54321', $enabled = true)
    {
        $country = $this->countryInformationAcquirer->getCountryInfo('US');
        $regions = $country->getAvailableRegions();
        $region = $regions[mt_rand(0, count($regions) - 1)];

        $name = uniqid(self::TEST_PREFIX, false);
        $description = 'This is an inventory source created by api-functional tests';
        $city = 'Exampletown';
        $street = 'Some Street 455';
        $contactName = 'Contact Name';
        $email = 'example.guy@test.com';
        $fax = '0120002066033';
        $phone = '01660002020044';
        $latitude = 51.343479;
        $longitude = 12.387772;
        $priority = mt_rand(1, 999);

        $carriers = [];
        for ($index = 1; $index <= $countCarrier; $index++) {
            $carrierCode = 'CAR-' . $index;
            $carrier = $this->sourceCarrierLinkFactory->create();
            $carrier->setPosition($index);
            $carrier->setCarrierCode($carrierCode);
            $carriers[] = $carrier;
        }

        /** @var  \Magento\InventoryApi\Api\Data\SourceInterface $source */
        $source = $this->sourceFactory->create();
        $source->setName($name);
        $source->setCity($city);
        $source->setPostcode($postcode);
        $source->setContactName($contactName);
        $source->setCountryId($country->getId());
        $source->setDescription($description);
        $source->setEmail($email);
        $source->setStreet($street);
        $source->setFax($fax);
        $source->setPhone($phone);
        $source->setRegion($region->getName());
        $source->setRegionId($region->getId());
        $source->setLatitude($latitude);
        $source->setLongitude($longitude);
        $source->setEnabled($enabled);
        $source->setPriority($priority);
        $source->setCarrierLinks($carriers);

        return $source;
    }

    /**
     * Update the given source in magento
     *
     * @param SourceInterface $expectedSource
     *
     * @return int
     */
    private function updateSource($expectedSource)
    {
        $requestData = [
            'source' => $this->getSourceDataArray($expectedSource)
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $expectedSource->getSourceId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        // call the webservice to update the source
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Create new Inventory Source using Web API and verify it's integrity.
     */
    public function testCreateSource()
    {
        $expectedSource = $this->createRandomSource(3);

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
            'source' => $this->getSourceDataArray($expectedSource)
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($result);

        $createdSource = $this->sourceRepository->get($result);
        $this->assertEquals(
            $this->getExpectedValues($this->getSourceDataArray($expectedSource)),
            $this->getSourceDataArray($createdSource)
        );
    }

    /**
     * Load already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testGetSource()
    {
        $expectedSource = $this->createRandomSource(5);
        $currentSourceId = $this->sourceRepository->save($expectedSource);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $currentSourceId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, [SourceInterface::SOURCE_ID => $currentSourceId]);
        $this->assertNotNull($result);

        $this->assertEquals($currentSourceId, $result[SourceInterface::SOURCE_ID]);

        unset($result[SourceInterface::SOURCE_ID]);
        $this->assertEquals($this->getSourceDataArray($expectedSource), $result);
    }

    /**
     * Load and verify integrity of a list of already existing Inventory Sources filtered by Search Criteria.
     */
    public function testGetSourcesList()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        $postcode1 = uniqid(self::TEST_PREFIX, false);
        $postcode2 = uniqid(self::TEST_PREFIX, false);

        $source1 = $this->createRandomSource(2, $postcode1, true);
        $this->sourceRepository->save($source1);

        $source2 = $this->createRandomSource(3, $postcode1, false);
        $this->sourceRepository->save($source2);

        $source3 = $this->createRandomSource(1, $postcode2, true);
        $this->sourceRepository->save($source3);

        $source4 = $this->createRandomSource(3, $postcode2, true);
        $this->sourceRepository->save($source4);

        //  add filters to find all active items with created postcode
        $postcodeFilter = implode(',', [$postcode1, $postcode2]);
        $searchCriteriaBuilder->addFilter('postcode', $postcodeFilter, 'in');
        $searchCriteriaBuilder->addFilter('enabled', 1, 'eq');

        $searchData = $searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'search?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $searchResult = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(3, count($searchResult['items']));
        $this->assertEquals(
            $searchResult['items'][0][SourceInterface::SOURCE_ID],
            $source1->getSourceId()
        );
        $this->assertEquals(
            $searchResult['items'][1][SourceInterface::SOURCE_ID],
            $source3->getSourceId()
        );

        /** @var SourceCarrierLinkInterface[] $carrierLinks */
        $resultCarrierLink = $searchResult['items'][0][SourceInterface::CARRIER_LINKS];

        $counter = 0;
        foreach ($source1->getCarrierLinks() as $carrierLink) {
            $carrierCode = $resultCarrierLink[$counter][SourceCarrierLinkInterface::CARRIER_CODE];
            $this->assertEquals($carrierLink->getCarrierCode(), $carrierCode);
            $counter++;
        }
    }

    /**
     * Update already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testUpdateSource()
    {
        // create a new source
        $expectedSource = $this->createRandomSource(2);
        $this->sourceRepository->save($expectedSource);

        // set name and city property's in the source to update them
        $expectedName = uniqid('UpdatedName_', false);
        $expectedCity = uniqid('UpdatedCity_', false);
        $expectedSource->setName($expectedName);
        $expectedSource->setCity($expectedCity);
        $updateSourceId = $this->updateSource($expectedSource);

        // verify it's integrity
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $updateSourceId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, [SourceInterface::SOURCE_ID => $updateSourceId]);
        $this->assertEquals($expectedName, $result[SourceInterface::NAME]);
        $this->assertEquals($expectedCity, $result[SourceInterface::CITY]);
    }

    /**
     * Update already existing Inventory Source, removing carrier links, using Web API and verify it's integrity.
     */
    public function testUpdateSourceWithoutCarriers()
    {
        $expectedSource = $this->createRandomSource(2);
        $this->sourceRepository->save($expectedSource);

        $carriers = [];
        $expectedSource->setCarrierLinks($carriers);
        $updateSourceId = $this->updateSource($expectedSource);

        // verify it's integrity
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $updateSourceId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, [SourceInterface::SOURCE_ID => $updateSourceId]);
        $this->assertEquals($carriers, $result[SourceInterface::CARRIER_LINKS]);
    }
}
