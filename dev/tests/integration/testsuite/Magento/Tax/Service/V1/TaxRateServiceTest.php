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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxRateService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRateServiceInterface');
        $this->taxRateBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
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
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRate', $taxRateServiceData);
        $this->assertEquals($taxData['country_id'], $taxRateServiceData->getCountryId());
        $this->assertEquals($taxData['region_id'], $taxRateServiceData->getRegionId());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['code'], $taxRateServiceData->getCode());
        $this->assertEquals($taxData['region_id'], $taxRateServiceData->getRegionId());
        $this->assertEquals($taxData['percentage_rate'], $taxRateServiceData->getPercentageRate());
        $this->assertEquals($taxData['zip_range']['from'], $taxRateServiceData->getZipRange()->getFrom());
        $this->assertEquals($taxData['zip_range']['to'], $taxRateServiceData->getZipRange()->getTo());
        $this->assertEquals('78765-78780', $taxRateServiceData->getPostcode());
        $this->assertNotNull($taxRateServiceData->getId());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
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
        $expectedErrorMessages = [
            'country_id is a required field.',
            'region_id is a required field.',
            'percentage_rate is a required field.',
            'code is a required field.'
        ];
        $expectedErrorMessages = array_merge($expectedErrorMessages, $errorMessages);
        $taxRate = $this->taxRateBuilder->populateWithArray($dataArray)->create();
        try {
            $this->taxRateService->createTaxRate($taxRate);
        } catch (InputException $exception) {
            $errors = $exception->getErrors();
            foreach ($errors as $key => $error) {
                $this->assertEquals($expectedErrorMessages[$key], $error->getMessage());
            }
            throw $exception;
        }
    }

    public function createDataProvider()
    {
        return [
            'invalidZipRange' => [
                ['zip_range' => ['from' => 'from', 'to' => 'to']],
                'error' => [
                    'Invalid value of "from" provided for the zip_from field.',
                    'Invalid value of "to" provided for the zip_to field.'
                ]
            ],
            'emptyZipRange' => [
                ['zip_range' => ['from' => '', 'to' => '']],
                'error' => [
                    'Invalid value of "" provided for the zip_from field.',
                    'Invalid value of "" provided for the zip_to field.'
                ]
            ],
            'empty' => [
                [],
                'error' => ['postcode is a required field.']
            ],
            'zipRangeAndPostcode' => [
                ['postcode' => 78727, 'zip_range' => ['from' => 78765, 'to' => 78780]],
                'error' => []
            ],
            'higherRange' => [
                ['zip_range' => ['from' => 78780, 'to' => 78765]],
                'error' => ['Range To should be equal or greater than Range From.']
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
}
