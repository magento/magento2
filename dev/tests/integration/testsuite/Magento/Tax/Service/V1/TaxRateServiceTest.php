<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Service\V1\Data\ZipRangeBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Service\V1\Data\TaxRate;

class TaxRateServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * TaxRate builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxRateBuilder
     */
    private $taxRateBuilder;

    /**
     * TaxRateService
     *
     * @var \Magento\Tax\Service\V1\TaxRateServiceInterface
     */
    private $taxRateService;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRateFixtureFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var  \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxRateService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRateServiceInterface');
        $this->taxRateBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $this->taxRateFixtureFactory = new TaxRuleFixtureFactory();
        $this->countryFactory = $this->objectManager->create('Magento\Directory\Model\CountryFactory');
        $this->regionFactory = $this->objectManager->create('Magento\Directory\Model\RegionFactory');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRate()
    {
        $taxData = [
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate' . rand(),
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        // Tax rate data object created
        $taxRate = $this->taxRateBuilder->populateWithArray($taxData)->create();
        //Tax rate service call
        $taxRateServiceData = $this->taxRateService->createTaxRate($taxRate);

        //Assertions
        $this->assertInstanceOf('Magento\Tax\Service\V1\Data\TaxRate', $taxRateServiceData);
        $this->assertEquals($taxData['country_id'], $taxRateServiceData->getCountryId());
        $this->assertEquals($taxData['region_id'], $taxRateServiceData->getRegionId());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['code'], $taxRateServiceData->getCode());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['zip_range']['from'], $taxRateServiceData->getZipRange()->getFrom());
        $this->assertEquals($taxData['zip_range']['to'], $taxRateServiceData->getZipRange()->getTo());
        $this->assertEquals('78765-78780', $taxRateServiceData->getPostcode());
        $this->assertNotNull($taxRateServiceData->getId());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/store.php
     */
    public function testCreateTaxRateWithTitles()
    {
        $store = $this->objectManager->get('Magento\Store\Model\Store');
        $store->load('test', 'code');

        $taxData = [
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate' . rand(),
            'zip_range' => ['from' => 78765, 'to' => 78780],
            'titles' => [
                [
                    'store_id' => $store->getId(),
                    'value' => 'random store title',
                ]
            ]
        ];
        // Tax rate data object created
        $taxRate = $this->taxRateBuilder->populateWithArray($taxData)->create();
        //Tax rate service call
        $taxRateServiceData = $this->taxRateService->createTaxRate($taxRate);

        //Assertions
        $this->assertInstanceOf('Magento\Tax\Service\V1\Data\TaxRate', $taxRateServiceData);
        $this->assertEquals($taxData['country_id'], $taxRateServiceData->getCountryId());
        $this->assertEquals($taxData['region_id'], $taxRateServiceData->getRegionId());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['code'], $taxRateServiceData->getCode());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['zip_range']['from'], $taxRateServiceData->getZipRange()->getFrom());
        $this->assertEquals($taxData['zip_range']['to'], $taxRateServiceData->getZipRange()->getTo());
        $this->assertEquals('78765-78780', $taxRateServiceData->getPostcode());
        $this->assertNotNull($taxRateServiceData->getId());

        $titles = $taxRateServiceData->getTitles();
        $this->assertEquals(1, count($titles));
        $this->assertEquals($store->getId(), $titles[0]->getStoreId());
        $this->assertEquals($taxData['titles'][0]['value'], $titles[0]->getValue());

        $taxRateServiceData = $this->taxRateService->getTaxRate($taxRateServiceData->getId());

        $titles = $taxRateServiceData->getTitles();
        $this->assertEquals(1, count($titles));
        $this->assertEquals($store->getId(), $titles[0]->getStoreId());
        $this->assertEquals($taxData['titles'][0]['value'], $titles[0]->getValue());
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage id is not expected for this request.
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRateWithId()
    {
        $invalidTaxData = [
            'id' => 2,
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate' . rand(),
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        $taxRate = $this->taxRateBuilder->populateWithArray($invalidTaxData)->create();
        $this->taxRateService->createTaxRate($taxRate);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Code already exists.
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRateDuplicateCodes()
    {
        $invalidTaxData = [
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate' . rand(),
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        $taxRate = $this->taxRateBuilder->populateWithArray($invalidTaxData)->create();
        //Service call initiated twice to add the same code
        $this->taxRateService->createTaxRate($taxRate);
        $this->taxRateService->createTaxRate($taxRate);
    }

    /**
     * @param array $dataArray
     * @param string $errorMessages
     * @throws \Magento\Framework\Exception\InputException
     *
     * @dataProvider createDataProvider
     * @expectedException \Magento\Framework\Exception\InputException
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxRateWithExceptionMessages($dataArray, $errorMessages)
    {
        $taxRate = $this->taxRateBuilder->populateWithArray($dataArray)->create();
        try {
            $this->taxRateService->createTaxRate($taxRate);
        } catch (InputException $exception) {
            $errors = $exception->getErrors();
            foreach ($errors as $key => $error) {
                $this->assertEquals($errorMessages[$key], $error->getMessage());
            }
            throw $exception;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function createDataProvider()
    {
        return [
            'invalidZipRange' => [
                ['zip_range' => ['from' => 'from', 'to' => 'to']],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'Invalid value of "from" provided for the zip_from field.',
                    'Invalid value of "to" provided for the zip_to field.'
                ]
            ],
            'emptyZipRange' => [
                ['zip_range' => ['from' => '', 'to' => '']],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'Invalid value of "" provided for the zip_from field.',
                    'Invalid value of "" provided for the zip_to field.'
                ]
            ],
            'empty' => [
                [],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'postcode is a required field.'
                ]
            ],
            'zipRangeAndPostcode' => [
                ['postcode' => 78727, 'zip_range' => ['from' => 78765, 'to' => 78780]],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.'
                ]
            ],
            'higherRange' => [
                ['zip_range' => ['from' => 78780, 'to' => 78765]],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'Range To should be equal or greater than Range From.'
                ]
            ],
            'invalidCountry' => [
                ['country_id' => 'XX'],
                'error' => [
                    'Invalid value of "XX" provided for the country_id field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'postcode is a required field.'
                ]
            ],
            'invalidCountry2' => [
                ['country_id' => ' '],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'postcode is a required field.'
                ]
            ],
            'invalidRegion1' => [
                ['region_id' => '-'],
                'error' => [
                    'country_id is a required field.',
                    'Invalid value of "-" provided for the region_id field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'postcode is a required field.'
                ]
            ],
            'spaceRegion' => [
                ['region_id' => ' '],
                'error' => [
                    'country_id is a required field.',
                    'percentage_rate is a required field.',
                    'code is a required field.',
                    'postcode is a required field.'
                ]
            ],
            'emptyPercentageRate' => [
                ['country_id' => 'US',
                    'region_id' => '8',
                    'percentage_rate' => '',
                    'code' => 'US-CA-*-Rate' . rand(),
                    'zip_range' => ['from' => 78765, 'to' => 78780]
                ],
                'error' => [
                    'percentage_rate is a required field.'
                ]
            ]

        ];
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxRate()
    {
        $data = [
            'tax_country_id' => 'US',
            'tax_region_id' => '12',
            'tax_postcode' => '*',
            'code' => 'US_12_Code',
            'rate' => '7.5'
        ];
        $rate = $this->objectManager->create('Magento\Tax\Model\Calculation\Rate')
            ->setData($data)
            ->save();

        $taxRate = $this->taxRateService->getTaxRate($rate->getId());

        $this->assertEquals('US', $taxRate->getCountryId());
        $this->assertEquals(12, $taxRate->getRegionId());
        $this->assertEquals('*', $taxRate->getPostcode());
        $this->assertEquals('US_12_Code', $taxRate->getCode());
        $this->assertEquals(7.5, $taxRate->getPercentageRate());
        $this->assertNull($taxRate->getZipRange());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with taxRateId = -1
     */
    public function testGetRateWithNoSuchEntityException()
    {
        $this->taxRateService->getTaxRate(-1);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateTaxRates()
    {
        /** @var ZipRangeBuilder $zipRangeBuilder */
        $zipRangeBuilder = $this->objectManager->get('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $taxRate = $this->taxRateBuilder
            ->setCountryId('US')
            ->setRegionId(42)
            ->setPercentageRate(8.25)
            ->setCode('UpdateTaxRates')
            ->setPostcode('78780')
            ->create();
        $taxRate = $this->taxRateService->createTaxRate($taxRate);
        $zipRange = $zipRangeBuilder->setFrom(78700)->setTo(78780)->create();
        $updatedTaxRate = $this->taxRateBuilder->populate($taxRate)
            ->setPostcode(null)
            ->setZipRange($zipRange)
            ->create();

        $this->taxRateService->updateTaxRate($updatedTaxRate);

        $retrievedRate = $this->taxRateService->getTaxRate($taxRate->getId());
        // Expect the service to have filled in the new postcode for us
        $updatedTaxRate = $this->taxRateBuilder->populate($updatedTaxRate)->setPostcode('78700-78780')->create();
        $this->assertEquals($retrievedRate->__toArray(), $updatedTaxRate->__toArray());
        $this->assertNotEquals($retrievedRate->__toArray(), $taxRate->__toArray());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage taxRateId =
     */
    public function testUpdateTaxRateNoId()
    {
        $taxRate = $this->taxRateBuilder
            ->setCountryId('US')
            ->setRegionId(42)
            ->setPercentageRate(8.25)
            ->setCode('UpdateTaxRates')
            ->setPostcode('78780')
            ->create();

        $this->taxRateService->updateTaxRate($taxRate);
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage postcode
     */
    public function testUpdateTaxRateMissingRequiredFields()
    {
        $taxRate = $this->taxRateBuilder
            ->setCountryId('US')
            ->setRegionId(42)
            ->setPercentageRate(8.25)
            ->setCode('UpdateTaxRates')
            ->setPostcode('78780')
            ->create();
        $taxRate = $this->taxRateService->createTaxRate($taxRate);
        $updatedTaxRate = $this->taxRateBuilder->populate($taxRate)
            ->setPostcode(null)
            ->create();

        $this->taxRateService->updateTaxRate($updatedTaxRate);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteTaxRate()
    {
        // Create a new tax rate
        $taxRateData = $this->taxRateBuilder
            ->setCode('TX')
            ->setCountryId('US')
            ->setPercentageRate(5)
            ->setPostcode(77000)
            ->setRegionId(1)
            ->create();
        $taxRateId = $this->taxRateService->createTaxRate($taxRateData)->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRateService->deleteTaxRate($taxRateId));

        // Get the new tax rate, this should fail
        try {
            $this->taxRateService->getTaxRate($taxRateId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRateId',
                'fieldValue' => $taxRateId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        } catch (\Exception $e) {
            $this->fail('Caught unexpected exception');
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteTaxRateException()
    {
        // Create a new tax rate
        $taxRateData = $this->taxRateBuilder
            ->setCode('TX')
            ->setCountryId('US')
            ->setPercentageRate(6)
            ->setPostcode(77001)
            ->setRegionId(1)
            ->create();
        $taxRateId = $this->taxRateService->createTaxRate($taxRateData)->getId();

        // Delete the new tax rate
        $this->assertTrue($this->taxRateService->deleteTaxRate($taxRateId));

        // Delete the new tax rate again, this should fail
        try {
            $this->taxRateService->deleteTaxRate($taxRateId);
            $this->fail('NoSuchEntityException expected but not thrown');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRateId',
                'fieldValue' => $taxRateId,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        } catch (\Exception $e) {
            $this->fail('Caught unexpected exception');
        }
    }

    /**
     *
     * @param \Magento\Framework\Service\V1\Data\Filter[] $filters
     * @param \Magento\Framework\Service\V1\Data\Filter[] $filterGroup
     * @param $expectedRateCodes
     *
     * @magentoDbIsolation enabled
     * @dataProvider searchTaxRatesDataProvider
     */
    public function testSearchTaxRates($filters, $filterGroup, $expectedRateCodes)
    {
        $taxRates = $this->taxRateFixtureFactory->createTaxRates(
            [
                ['percentage' => 7.5, 'country' => 'US', 'region' => '42'],
                ['percentage' => 7.5, 'country' => 'US', 'region' => '12'],
                ['percentage' => 22.0, 'country' => 'US', 'region' => '42'],
                ['percentage' => 10.0, 'country' => 'US', 'region' => '12']
            ]
        );

        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Service\V1\Data\SearchCriteriaBuilder');
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }
        $searchCriteria = $searchBuilder->create();

        $searchResults = $this->taxRateService->searchTaxRates($searchCriteria);

        $items = [];
        foreach ($expectedRateCodes as $rateCode) {
            $rateId = $taxRates[$rateCode];
            $items[] = $this->taxRateService->getTaxRate($rateId);
        }

        $resultsBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Tax\Service\V1\Data\TaxRateSearchResultsBuilder');
        $expectedResult = $resultsBuilder->setItems($items)
            ->setTotalCount(count($items))
            ->setSearchCriteria($searchCriteria)
            ->create();

        $this->assertEquals($expectedResult, $searchResults);
    }

    public function searchTaxRatesDataProvider()
    {
        $filterBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Service\V1\Data\FilterBuilder');

        return [
            'eq' => [
                [$filterBuilder->setField(TaxRate::KEY_REGION_ID)->setValue(42)->create()],
                null,
                ['US - 42 - 7.5', 'US - 42 - 22']
            ],
            'and' => [
                [
                    $filterBuilder->setField(TaxRate::KEY_REGION_ID)->setValue(42)->create(),
                    $filterBuilder->setField(TaxRate::KEY_PERCENTAGE_RATE)->setValue(22.0)->create(),
                ],
                [],
                ['US - 42 - 22']
            ],
            'or' => [
                [],
                [
                    $filterBuilder->setField(TaxRate::KEY_PERCENTAGE_RATE)->setValue(22.0)->create(),
                    $filterBuilder->setField(TaxRate::KEY_PERCENTAGE_RATE)->setValue(10.0)->create(),
                ],
                ['US - 42 - 22', 'US - 12 - 10']
            ],
            'like' => [
                [
                    $filterBuilder->setField(TaxRate::KEY_CODE)->setValue('%7.5')->setConditionType('like')
                        ->create()
                ],
                [],
                ['US - 42 - 7.5', 'US - 12 - 7.5']
            ]
        ];
    }

}
