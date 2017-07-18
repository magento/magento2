<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Tax\Model\Calculation\Rate;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRateRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = "taxTaxRateRepositoryV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH = "/V1/taxRates";

    /** @var \Magento\Tax\Model\Calculation\Rate[] */
    private $fixtureTaxRates;

    /** @var \Magento\Tax\Model\ClassModel[] */
    private $fixtureTaxClasses;

    /** @var \Magento\Tax\Model\Calculation\Rule[] */
    private $fixtureTaxRules;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    private $taxRateService;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var  SortOrderBuilder */
    private $sortOrderBuilder;

    /**
     * Other rates created during tests, to be deleted in tearDown()
     *
     * @var \Magento\Tax\Model\Calculation\Rate[]
     */
    private $otherRates = [];

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->taxRateService = $objectManager->get(\Magento\Tax\Api\TaxRateRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->sortOrderBuilder = $objectManager->create(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        /** Initialize tax classes, tax rates and tax rules defined in fixture Magento/Tax/_files/tax_classes.php */
        $this->getFixtureTaxRates();
        $this->getFixtureTaxClasses();
        $this->getFixtureTaxRules();
    }

    public function tearDown()
    {
        $taxRules = $this->getFixtureTaxRules();
        if (count($taxRules)) {
            $taxRates = $this->getFixtureTaxRates();
            $taxClasses = $this->getFixtureTaxClasses();
            foreach ($taxRules as $taxRule) {
                $taxRule->delete();
            }
            foreach ($taxRates as $taxRate) {
                $taxRate->delete();
            }
            foreach ($taxClasses as $taxClass) {
                $taxClass->delete();
            }
        }
        if (count($this->otherRates)) {
            foreach ($this->otherRates as $taxRate) {
                $taxRate->delete();
            }
        }
    }

    public function testCreateTaxRateExistingCode()
    {
        $expectedMessage = '%1 already exists.';
        $data = [
            'tax_rate' => [
                'tax_country_id' => 'US',
                'tax_region_id' => 12,
                'tax_postcode' => '*',
                'code' => 'US-CA-*-Rate 1',
                'rate' => '8.2501',
            ],
        ];

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
        try {
            $this->_webApiCall($serviceInfo, $data);
            $this->fail('Expected exception was not raised');
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['Code'], $errorObj['parameters']);
        }
    }

    public function testCreateTaxRateWithoutValue()
    {
        $data = [
            'tax_rate' => [
                'tax_country_id' => 'US',
                'tax_region_id' => 12,
                'tax_postcode' => '*',
                'code' => 'US-CA-*-Rate 1',
            ],
        ];

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
        try {
            $this->_webApiCall($serviceInfo, $data);
            $this->fail('Expected exception was not raised');
        } catch (\SoapFault $e) {
            $this->assertContains(
                'SOAP-ERROR: Encoding: object has no \'rate\' property',
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals('%fieldName is a required field.', $errorObj['message']);
            $this->assertEquals(['fieldName' => 'percentage_rate'], $errorObj['parameters']);
        }
    }

    public function testCreateTaxRate()
    {
        $data = [
            'tax_rate' => [
                'tax_country_id' => 'US',
                'tax_region_id' => 12,
                'tax_postcode' => '*',
                'code' => 'Test Tax Rate ' . microtime(),
                'rate' => '8.2501',
            ],
        ];

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
        $result = $this->_webApiCall($serviceInfo, $data);
        $this->assertArrayHasKey('id', $result);
        $taxRateId = $result['id'];
        /** Ensure that tax rate was actually created in DB */
        /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
        $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
        $this->assertEquals($taxRateId, $taxRate->load($taxRateId)->getId(), 'Tax rate was not created in  DB.');
        $taxRate->delete();
    }

    public function testCreateTaxRateWithZipRange()
    {
        $data = [
            'tax_rate' => [
                'tax_country_id' => 'US',
                'tax_region_id' => 12,
                'code' => 'Test Tax Rate ' . microtime(),
                'rate' => '8.2501',
                'zip_is_range' => 1,
                'zip_from' => 17,
                'zip_to' => 25,
            ],
        ];

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
        $result = $this->_webApiCall($serviceInfo, $data);
        $this->assertArrayHasKey('id', $result);
        $taxRateId = $result['id'];
        /** Ensure that tax rate was actually created in DB */
        /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
        $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
        $this->assertEquals($taxRateId, $taxRate->load($taxRateId)->getId(), 'Tax rate was not created in  DB.');
        $this->assertEquals('17-25', $taxRate->getTaxPostcode(), 'Zip range is not saved in DB.');
        $taxRate->delete();
    }

    public function testCreateTaxRateWithZeroValue()
    {
        $data = [
            'tax_rate' => [
                'tax_country_id' => 'US',
                'tax_region_id' => 12,
                'tax_postcode' => '*',
                'code' => 'Test Tax Rate ' . microtime(),
                'rate' => '0.0',
            ],
        ];

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
        $result = $this->_webApiCall($serviceInfo, $data);
        $this->assertArrayHasKey('id', $result);
        $taxRateId = $result['id'];
        /** Ensure that tax rate was actually created in DB */
        /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
        $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
        $taxModel = $taxRate->load($taxRateId);
        $this->assertEquals($taxRateId, $taxModel->getId(), 'Tax rate was not created in DB.');
        $this->assertEquals(0, $taxModel->getRate(), 'Tax rate value is wrong.');
        $taxRate->delete();
    }

    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testUpdateTaxRate()
    {
        $fixtureRate = $this->getFixtureTaxRates()[0];

        $data = [
            'tax_rate' => [
                'id' => $fixtureRate->getId(),
                'tax_region_id' => 43,
                'tax_country_id' => 'US',
                'tax_postcode' => '07400',
                'code' => 'Test Tax Rate ' . microtime(),
                'rate' => 3.456,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, $data);
        $expectedRateData = $data['tax_rate'];
        /** Ensure that tax rate was actually updated in DB */
        /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
        $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
        $taxRateModel = $taxRate->load($fixtureRate->getId());
        $this->assertEquals($expectedRateData['id'], $taxRateModel->getId(), 'Tax rate was not updated in  DB.');
        $this->assertEquals(
            $expectedRateData['tax_region_id'],
            $taxRateModel->getTaxRegionId(),
            'Tax rate was not updated in  DB.'
        );
        $this->assertEquals(
            $expectedRateData['tax_country_id'],
            $taxRateModel->getTaxCountryId(),
            'Tax rate was not updated in  DB.'
        );
        $this->assertEquals(
            $expectedRateData['tax_postcode'],
            $taxRateModel->getTaxPostcode(),
            'Tax rate was not updated in  DB.'
        );
        $this->assertEquals($expectedRateData['code'], $taxRateModel->getCode(), 'Tax rate was not updated in  DB.');
        $this->assertEquals(
            $expectedRateData['rate'],
            $taxRateModel->getRate(),
            'Tax rate was not updated in  DB.'
        );
    }

    public function testUpdateTaxRateNotExisting()
    {
        $data = [
            'tax_rate' => [
                'id' => 555,
                'tax_region_id' => 43,
                'tax_country_id' => 'US',
                'tax_postcode' => '07400',
                'code' => 'Test Tax Rate ' . microtime(),
                'rate' => 3.456,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo, $data);
            $this->fail('Expected exception was not raised');
        } catch (\Exception $e) {
            $expectedMessage = 'No such entity with %fieldName = %fieldValue';

            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
        }
    }

    public function testGetTaxRate()
    {
        $taxRateId = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRateId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['rateId' => $taxRateId]);
        $expectedRateData = [
            'id' => 2,
            'tax_country_id' => 'US',
            'tax_region_id' => 43,
            'tax_postcode' => '*',
            'code' => 'US-NY-*-Rate 1',
            'rate' => 8.375,
            'titles' => [],
            'region_name' => 'NY',
        ];
        $this->assertEquals($expectedRateData, $result);
    }

    public function testGetTaxRateNotExist()
    {
        $taxRateId = 37865;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRateId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo, ['rateId' => $taxRateId]);
            $this->fail('Expected exception was not raised');
        } catch (\Exception $e) {
            $expectedMessage = 'No such entity with %fieldName = %fieldValue';

            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
        }
    }

    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRate()
    {
        /** Tax rules must be deleted since tax rate cannot be deleted if there are any tax rules associated with it */
        $taxRules = $this->getFixtureTaxRules();
        foreach ($taxRules as $taxRule) {
            $taxRule->delete();
        }

        $fixtureRate = $this->getFixtureTaxRates()[0];
        $taxRateId = $fixtureRate->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRateId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['rateId' => $taxRateId]);
        $this->assertTrue($result);
        /** Ensure that tax rate was actually removed from DB */
        /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
        $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
        $this->assertNull($taxRate->load($taxRateId)->getId(), 'Tax rate was not deleted from DB.');
    }

    /**
     * Insure that tax rate cannot be deleted if it is used for a tax rule.
     *
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCannotDeleteTaxRate()
    {
        $fixtureRate = $this->getFixtureTaxRates()[0];
        $taxRateId = $fixtureRate->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRateId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo, ['rateId' => $taxRateId]);
            $this->fail('Expected exception was not raised');
        } catch (\Exception $e) {
            $expectedMessage = 'The tax rate cannot be removed. It exists in a tax rule.';

            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
        }
    }

    public function testSearchTaxRates()
    {
        $rates = $this->setupTaxRatesForSearch();

        // Find rates whose code is 'codeUs12'
        $filter = $this->filterBuilder->setField(Rate::KEY_CODE)
            ->setValue('codeUs12')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);

        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        /** @var \Magento\Framework\Api\SearchResults $searchResults */
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(1, $searchResults['total_count']);

        $expectedRuleData = [
            [
                'id' => (int)$rates['codeUs12']->getId(),
                'tax_country_id' => $rates['codeUs12']->getTaxCountryId(),
                'tax_region_id' => (int)$rates['codeUs12']->getTaxRegionId(),
                'region_name' => 'CA',
                'tax_postcode' => $rates['codeUs12']->getTaxPostcode(),
                'code' =>  $rates['codeUs12']->getCode(),
                'rate' => ((float) $rates['codeUs12']->getRate()),
                'titles' => [],
            ],
        ];
        $this->assertEquals($expectedRuleData, $searchResults['items']);
    }

    public function testSearchTaxRatesCz()
    {
        // TODO: This test fails in SOAP, a generic bug searching in SOAP
        $this->_markTestAsRestOnly();
        $rates = $this->setupTaxRatesForSearch();

        $filterBR = $this->filterBuilder->setField(Rate::KEY_COUNTRY_ID)
            ->setValue('BR')
            ->create();
        $filterUS = $this->filterBuilder->setField(Rate::KEY_COUNTRY_ID)
            ->setValue('US')
            ->create();
        // Find rates which country id 'CZ'
        $filterCZ = $this->filterBuilder->setField(Rate::KEY_COUNTRY_ID)
            ->setValue('CZ')
            ->create();
        $sortOrder = $this->sortOrderBuilder
            ->setField(Rate::KEY_POSTCODE)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $filterRate = $this->filterBuilder->setField(Rate::KEY_PERCENTAGE_RATE)
            ->setValue('2.2000')
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filterBR, $filterUS, $filterCZ]);
        // Order them by descending postcode (not the default order)
        $this->searchCriteriaBuilder->addFilters([$filterCZ, $filterRate])
            ->addSortOrder($sortOrder);
        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        /** @var \Magento\Framework\Api\SearchResults $searchResults */
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(2, $searchResults['total_count']);

        $expectedRuleData = [
            [
                'id' => (int)$rates['codeCz2']->getId(),
                'tax_country_id' => $rates['codeCz2']->getTaxCountryId(),
                'tax_postcode' => $rates['codeCz2']->getTaxPostcode(),
                'code' =>  $rates['codeCz2']->getCode(),
                'rate' =>  ((float) $rates['codeCz2']->getRate()),
                'tax_region_id' => 0,
                'titles' => [],
            ],
            [
                'id' => (int)$rates['codeCz1']->getId(),
                'tax_country_id' => $rates['codeCz1']->getTaxCountryId(),
                'tax_postcode' => $rates['codeCz1']->getTaxPostcode(),
                'code' => $rates['codeCz1']->getCode(),
                'rate' => ((float) $rates['codeCz1']->getRate()),
                'tax_region_id' => 0,
                'titles' => [],
            ],
        ];
        $this->assertEquals($expectedRuleData, $searchResults['items']);
    }

    /**
     * Get tax rates created in Magento\Tax\_files\tax_classes.php
     *
     * @return \Magento\Tax\Model\Calculation\Rate[]
     */
    private function getFixtureTaxRates()
    {
        if ($this->fixtureTaxRates === null) {
            $this->fixtureTaxRates = [];
            if ($this->getFixtureTaxRules()) {
                $taxRateIds = (array)$this->getFixtureTaxRules()[0]->getRates();
                foreach ($taxRateIds as $taxRateId) {
                    /** @var \Magento\Tax\Model\Calculation\Rate $taxRate */
                    $taxRate = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rate::class);
                    $this->fixtureTaxRates[] = $taxRate->load($taxRateId);
                }
            }
        }
        return $this->fixtureTaxRates;
    }

    /**
     * Get tax classes created in Magento\Tax\_files\tax_classes.php
     *
     * @return \Magento\Tax\Model\ClassModel[]
     */
    private function getFixtureTaxClasses()
    {
        if ($this->fixtureTaxClasses === null) {
            $this->fixtureTaxClasses = [];
            if ($this->getFixtureTaxRules()) {
                $taxClassIds = array_merge(
                    (array)$this->getFixtureTaxRules()[0]->getCustomerTaxClasses(),
                    (array)$this->getFixtureTaxRules()[0]->getProductTaxClasses()
                );
                foreach ($taxClassIds as $taxClassId) {
                    /** @var \Magento\Tax\Model\ClassModel $taxClass */
                    $taxClass = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\ClassModel::class);
                    $this->fixtureTaxClasses[] = $taxClass->load($taxClassId);
                }
            }
        }
        return $this->fixtureTaxClasses;
    }

    /**
     * Get tax rule created in Magento\Tax\_files\tax_classes.php
     *
     * @return \Magento\Tax\Model\Calculation\Rule[]
     */
    private function getFixtureTaxRules()
    {
        if ($this->fixtureTaxRules === null) {
            $this->fixtureTaxRules = [];
            $taxRuleCodes = ['Test Rule Duplicate', 'Test Rule'];
            foreach ($taxRuleCodes as $taxRuleCode) {
                /** @var \Magento\Tax\Model\Calculation\Rule $taxRule */
                $taxRule = Bootstrap::getObjectManager()->create(\Magento\Tax\Model\Calculation\Rule::class);
                $taxRule->load($taxRuleCode, 'code');
                if ($taxRule->getId()) {
                    $this->fixtureTaxRules[] = $taxRule;
                }
            }
        }
        return $this->fixtureTaxRules;
    }

    /**
     * Creates rates for search tests.
     *
     * @return \Magento\Tax\Model\Calculation\Rate[]
     */
    private function setupTaxRatesForSearch()
    {
        $objectManager = Bootstrap::getObjectManager();

        $taxRateUs12 = [
            'tax_country_id' => 'US',
            'tax_region_id' => 12,
            'tax_postcode' => '*',
            'code' => 'codeUs12',
            'rate' => 22,
            'region_name' => 'CA',
        ];
        $rates['codeUs12'] = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->setData($taxRateUs12)
            ->save();

        $taxRateUs14 = [
            'tax_country_id' => 'US',
            'tax_region_id' => 14,
            'tax_postcode' => '*',
            'code' => 'codeUs14',
            'rate' => 22,
        ];
        $rates['codeUs14'] = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->setData($taxRateUs14)
            ->save();
        $taxRateBr13 = [
            'tax_country_id' => 'BR',
            'tax_region_id' => 13,
            'tax_postcode' => '*',
            'code' => 'codeBr13',
            'rate' => 7.5,
        ];
        $rates['codeBr13'] = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->setData($taxRateBr13)
            ->save();

        $taxRateCz1 = [
            'tax_country_id' => 'CZ',
            'tax_postcode' => '110 00',
            'code' => 'codeCz1',
            'rate' => 1.1,
        ];
        $rates['codeCz1'] = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->setData($taxRateCz1)
            ->save();
        $taxRateCz2 = [
            'tax_country_id' => 'CZ',
            'tax_postcode' => '250 00',
            'code' => 'codeCz2',
            'rate' => 2.2,
        ];
        $rates['codeCz2'] = $objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->setData($taxRateCz2)
            ->save();

        // Set class variable so rates will be deleted on tearDown()
        $this->otherRates = $rates;
        return $rates;
    }
}
