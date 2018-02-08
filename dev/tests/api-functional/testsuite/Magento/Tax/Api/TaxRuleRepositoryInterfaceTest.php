<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as HttpConstants;

class TaxRuleRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = "taxTaxRuleRepositoryV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH = "/V1/taxRules";

    /** @var \Magento\Tax\Model\Calculation\Rate[] */
    private $fixtureTaxRates;

    /** @var \Magento\Tax\Model\ClassModel[] */
    private $fixtureTaxClasses;

    /** @var \Magento\Tax\Model\Calculation\Rule[] */
    private $fixtureTaxRules;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->filterBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\FilterBuilder'
        );
        $objectManager = Bootstrap::getObjectManager();

        $this->searchCriteriaBuilder = $objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->filterBuilder = $objectManager->create(
            'Magento\Framework\Api\FilterBuilder'
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
    }

    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testDeleteTaxRule()
    {
        $fixtureRule = $this->getFixtureTaxRules()[0];
        $taxRuleId = $fixtureRule->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRuleId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        $requestData = ['ruleId' => $taxRuleId];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);
        /** Ensure that tax rule was actually removed from DB */
        /** @var \Magento\Tax\Model\Calculation\Rule $taxRule */
        $taxRate = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation\Rate');
        $this->assertNull($taxRate->load($taxRuleId)->getId(), 'Tax rule was not deleted from DB.');
    }

    public function testCreateTaxRule()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => HttpConstants::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = [
            'rule' => [
                'code' => 'Test Rule ' . microtime(),
                'position' => 10,
                'priority' => 5,
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => [1, 2],
                'calculate_subtotal' => 1,
            ],
        ];
        $taxRuleData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertArrayHasKey('id', $taxRuleData, "Tax rule ID is expected");
        $this->assertGreaterThan(0, $taxRuleData['id']);
        $taxRuleId = $taxRuleData['id'];
        unset($taxRuleData['id']);
        $this->assertEquals($requestData['rule'], $taxRuleData, "Tax rule is created with invalid data.");
        /** Ensure that tax rule was actually created in DB */
        /** @var \Magento\Tax\Model\Calculation\Rule $taxRule */
        $taxRule = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation\Rule');
        $this->assertEquals($taxRuleId, $taxRule->load($taxRuleId)->getId(), 'Tax rule was not created in DB.');
        $taxRule->delete();
    }

    public function testCreateTaxRuleInvalidTaxClassIds()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => HttpConstants::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = [
            'rule' => [
                'code' => 'Test Rule ' . microtime(),
                'position' => 10,
                'priority' => 5,
                'customer_tax_class_ids' => [2],
                'product_tax_class_ids' => [3],
                'tax_rate_ids' => [1, 2],
                'calculate_subtotal' => 1,
            ],
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Did not throw expected InputException');
        } catch (\SoapFault $e) {
            $this->assertContains('No such entity with customer_tax_class_ids = %fieldValue', $e->getMessage());
        } catch (\Exception $e) {
            $this->assertContains('No such entity with customer_tax_class_ids = %fieldValue', $e->getMessage());
        }
    }

    public function testCreateTaxRuleExistingCode()
    {
        $expectedMessage = '%1 already exists.';
        $requestData = [
            'rule' => [
                'code' => 'Test Rule ' . microtime(),
                'position' => 10,
                'priority' => 5,
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => [1, 2],
                'calculate_subtotal' => 0,
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
        $newTaxRuleData = $this->_webApiCall($serviceInfo, $requestData);
        try {
            $this->_webApiCall($serviceInfo, $requestData);
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

        // Clean up the new tax rule so it won't affect other tests
        /** @var \Magento\Tax\Model\Calculation\Rule $taxRule */
        $taxRule = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation\Rule');
        $taxRule->load($newTaxRuleData['id']);
        $taxRule->delete();
    }

    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetTaxRule()
    {
        $fixtureRule = $this->getFixtureTaxRules()[0];
        $taxRuleId = $fixtureRule->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRuleId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $expectedRuleData = [
            'id' => $taxRuleId,
            'code' => 'Test Rule Duplicate',
            'priority' => '0',
            'position' => '0',
            'customer_tax_class_ids' => array_values(array_unique($fixtureRule->getCustomerTaxClasses())),
            'product_tax_class_ids' => array_values(array_unique($fixtureRule->getProductTaxClasses())),
            'tax_rate_ids' => array_values(array_unique($fixtureRule->getRates())),
            'calculate_subtotal' => false,
        ];
        $requestData = ['ruleId' => $taxRuleId];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($expectedRuleData, $result);
    }
    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testSearchTaxRulesSimple()
    {
        // Find rules whose code is 'Test Rule'
        $filter = $this->filterBuilder->setField('code')
            ->setValue('Test Rule')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);

        $fixtureRule = $this->getFixtureTaxRules()[1];

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
                'id' => $fixtureRule->getId(),
                'code' => 'Test Rule',
                'priority' => 0,
                'position' => 0,
                'calculate_subtotal' => 0,
                'customer_tax_class_ids' => array_values(array_unique($fixtureRule->getCustomerTaxClasses())),
                'product_tax_class_ids' => array_values(array_unique($fixtureRule->getProductTaxClasses())),
                'tax_rate_ids' => array_values(array_unique($fixtureRule->getRates())),
            ],
        ];
        $this->assertEquals($expectedRuleData, $searchResults['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testSearchTaxRulesCodeLike()
    {
        // Find rules whose code starts with 'Test Rule'
        $filter = $this->filterBuilder
            ->setField('code')
            ->setValue('Test Rule%')
            ->setConditionType('like')
            ->create();

        $sortFilter = $this->filterBuilder
            ->setField('position')
            ->setValue(0)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter, $sortFilter]);

        $fixtureRule = $this->getFixtureTaxRules()[1];

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
                'id' => $fixtureRule->getId(),
                'code' => 'Test Rule',
                'priority' => 0,
                'position' => 0,
                'calculate_subtotal' => 0,
                'customer_tax_class_ids' => array_values(array_unique($fixtureRule->getCustomerTaxClasses())),
                'product_tax_class_ids' => array_values(array_unique($fixtureRule->getProductTaxClasses())),
                'tax_rate_ids' => array_values(array_unique($fixtureRule->getRates())),
            ],
            [
                'id' => $this->getFixtureTaxRules()[0]->getId(),
                'code' => 'Test Rule Duplicate',
                'priority' => 0,
                'position' => 0,
                'calculate_subtotal' => 0,
                'customer_tax_class_ids' => array_values(array_unique($fixtureRule->getCustomerTaxClasses())),
                'product_tax_class_ids' => array_values(array_unique($fixtureRule->getProductTaxClasses())),
                'tax_rate_ids' => array_values(array_unique($fixtureRule->getRates()))
            ],
        ];
        $this->assertEquals($expectedRuleData, $searchResults['items']);
    }

    public function testGetTaxRuleNotExist()
    {
        $taxRuleId = 37865;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$taxRuleId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $requestData = ['ruleId' => $taxRuleId];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
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
    public function testUpdateTaxRule()
    {
        $fixtureRule = $this->getFixtureTaxRules()[0];
        $requestData = [
            'rule' => [
                'id' => $fixtureRule->getId(),
                'code' => 'Test Rule ' . microtime(),
                'position' => 10,
                'priority' => 5,
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => [1, 2],
                'calculate_subtotal' => 1,
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
        $this->_webApiCall($serviceInfo, $requestData);
        $expectedRuleData = $requestData['rule'];
        /** Ensure that tax rule was actually updated in DB */
        /** @var \Magento\Tax\Model\Calculation $taxCalculation */
        $taxCalculation = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation');
        /** @var \Magento\Tax\Model\Calculation\Rule $taxRule */
        $taxRule = Bootstrap::getObjectManager()->create(
            'Magento\Tax\Model\Calculation\Rule',
            ['calculation' => $taxCalculation]
        );
        $taxRuleModel = $taxRule->load($fixtureRule->getId());
        $this->assertEquals($expectedRuleData['id'], $taxRuleModel->getId(), 'Tax rule was not updated in DB.');
        $this->assertEquals(
            $expectedRuleData['code'],
            $taxRuleModel->getCode(),
            'Tax rule code was updated incorrectly.'
        );
        $this->assertEquals(
            $expectedRuleData['position'],
            $taxRuleModel->getPosition(),
            'Tax rule sort order was updated incorrectly.'
        );
        $this->assertEquals(
            $expectedRuleData['priority'],
            $taxRuleModel->getPriority(),
            'Tax rule priority was updated incorrectly.'
        );
        $this->assertEquals(
            $expectedRuleData['customer_tax_class_ids'],
            array_values(array_unique($taxRuleModel->getCustomerTaxClasses())),
            'Customer Tax classes were updated incorrectly'
        );
        $this->assertEquals(
            $expectedRuleData['product_tax_class_ids'],
            array_values(array_unique($taxRuleModel->getProductTaxClasses())),
            'Product Tax classes were updated incorrectly.'
        );
        $this->assertEquals(
            $expectedRuleData['tax_rate_ids'],
            array_values(array_unique($taxRuleModel->getRates())),
            'Tax rates were updated incorrectly.'
        );
    }
    public function testUpdateTaxRuleNotExisting()
    {
        $requestData = [
            'rule' => [
                'id' => 12345,
                'code' => 'Test Rule ' . microtime(),
                'position' => 10,
                'priority' => 5,
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => [1, 2],
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
            $this->_webApiCall($serviceInfo, $requestData);
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
    public function testSearchTaxRule()
    {
        $fixtureRule = $this->getFixtureTaxRules()[0];


        $filter = $this->filterBuilder->setField('code')
            ->setValue($fixtureRule->getCode())
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
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($fixtureRule->getId(), $searchResults['items'][0]["id"]);
        $this->assertEquals($fixtureRule->getCode(), $searchResults['items'][0]['code']);
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
                    $taxRate = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation\Rate');
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
                    $taxClass = Bootstrap::getObjectManager()->create('Magento\Tax\Model\ClassModel');
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
                $taxRule = Bootstrap::getObjectManager()->create('Magento\Tax\Model\Calculation\Rule');
                $taxRule->load($taxRuleCode, 'code');
                if ($taxRule->getId()) {
                    $this->fixtureTaxRules[] = $taxRule;
                }
            }
        }
        return $this->fixtureTaxRules;
    }
}
