<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class RateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @dataProvider ajaxSaveActionDataProvider
     * @magentoDbIsolation enabled
     */
    public function testAjaxSaveAction($postData, $expectedData)
    {
        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/tax/rate/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        )->jsonDecode(
            $jsonBody
        );

        $this->assertArrayHasKey('tax_calculation_rate_id', $result);

        $rateId = $result['tax_calculation_rate_id'];
        /** @var $rate \Magento\Tax\Model\Calculation\Rate */
        $rate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Tax\Model\Calculation\Rate::class
        )->load(
            $rateId,
            'tax_calculation_rate_id'
        );

        $this->assertEquals($expectedData['zip_is_range'], $rate->getZipIsRange());
        $this->assertEquals($expectedData['zip_from'], $rate->getZipFrom());
        $this->assertEquals($expectedData['zip_to'], $rate->getZipTo());
        $this->assertEquals($expectedData['tax_postcode'], $rate->getTaxPostcode());
    }

    /**
     * Data provider for testAjaxSaveAction
     *
     * @return array
     */
    public function ajaxSaveActionDataProvider()
    {
        $postData = ['rate' => '10', 'tax_country_id' => 'US', 'tax_region_id' => '1'];
        return [
            [
                $postData + [
                    'code' => 'Rate ' . uniqid(rand()),
                    'zip_is_range' => '1',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ],
                ['zip_is_range' => 1, 'zip_from' => '10000', 'zip_to' => '20000', 'tax_postcode' => '10000-20000'],
            ],
            [
                $postData + [
                    'code' => 'Rate ' . uniqid(rand()),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ],
                ['zip_is_range' => null, 'zip_from' => null, 'zip_to' => null, 'tax_postcode' => '*']
            ]
        ];
    }

    /**
     * Test wrong data conditions
     *
     * @dataProvider ajaxSaveActionDataInvalidDataProvider
     * @magentoDbIsolation enabled
     */
    public function testAjaxSaveActionInvalidData($postData, $expectedData)
    {
        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/tax/rate/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        )->jsonDecode(
            $jsonBody
        );

        $this->assertEquals($expectedData['success'], $result['success']);
        $this->assertArrayHasKey('error_message', $result);
        $this->assertGreaterThan(1, strlen($result['error_message']));
    }

    /**
     * Data provider for testAjaxSaveActionInvalidData
     *
     * @return array
     */
    public function ajaxSaveActionDataInvalidDataProvider()
    {
        $expectedData = [
            'success' => false,
            'error_message' => 'Make sure all required information is valid.',
        ];
        return [
            [
                // Zip as range but no range values provided
                [
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '1',
                    'zip_from' => '',
                    'zip_to' => '',
                    'tax_postcode' => '*'
                ],
                $expectedData,
            ],
            // Code is empty
            [
                [
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => '',
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ],
                $expectedData
            ],
            // Country ID empty
            [
                [
                    'rate' => rand(1, 10000),
                    'tax_country_id' => '',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ],
                $expectedData
            ],
            // Rate empty
            [
                [
                    'rate' => '',
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ],
                $expectedData
            ],
            // Tax zip code is empty
            [
                [
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '',
                ],
                $expectedData
            ],
            // All params empty
            [
                [
                    'rate' => '',
                    'tax_country_id' => '',
                    'tax_region_id' => '1',
                    'code' => '',
                    'zip_is_range' => '0',
                    'zip_from' => '',
                    'zip_to' => '',
                    'tax_postcode' => '',
                ],
                $expectedData
            ]
        ];
    }

    /**
     * @dataProvider ajaxSaveActionDataProvider
     * @magentoDbIsolation enabled
     *
     * @param array $rateClassData
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testAjaxLoadAction($rateClassData)
    {
        /** @var \Magento\Tax\Api\Data\TaxRateInterfaceFactory $rateClassFactory */
        $rateClassFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Tax\Api\Data\TaxRateInterfaceFactory::class
        );

        $rateClass = $rateClassFactory->create();
        $rateClass->setRate($rateClassData['rate'])
                  ->setTaxCountryId($rateClassData['tax_country_id'])
                  ->setTaxRegionId($rateClassData['tax_region_id'])
                  ->setCode($rateClassData['code'])
                  ->setZipFrom($rateClassData['zip_from'])
                  ->setZipIsRange($rateClassData['zip_is_range'])
                  ->setZipFrom($rateClassData['zip_from'])
                  ->setZipTo($rateClassData['zip_to'])
                  ->setTaxPostcode($rateClassData['tax_postcode']);

        $rateClass->save($rateClass);

        $rateClassId=$rateClass->getTaxCalculationRateId();
        /** @var $class \Magento\Tax\Model\Calculation\Rate */
        $class = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Tax\Model\Calculation\Rate::class)
            ->load($rateClassId, 'tax_calculation_rate_id');

        $this->assertEquals($rateClassData['tax_country_id'], $class->getTaxCountryId());
        $this->assertEquals($rateClassData['tax_region_id'], $class->getTaxRegionId());
        $this->assertEquals($rateClassData['code'], $class->getCode());
        $this->assertEquals($rateClassData['rate'], $class->getRate());
        $this->assertEquals($rateClassData['zip_is_range']==1 ? 1 : 0, $class->getZipIsRange() ? 1 : 0);
        if ($rateClassData['zip_is_range']=='1') {
            $this->assertEquals($rateClassData['zip_from'], $class->getZipFrom());
            $this->assertEquals($rateClassData['zip_to'], $class->getZipTo());
        }

        $postData = [ 'id' => $rateClassId ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/tax/rate/ajaxLoad');
        $jsonBody = $this->getResponse()->getBody();

        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        )->jsonDecode(
            $jsonBody
        );

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success'] == true);
        $this->assertArrayHasKey('result', $result);
        $this->assertTrue(is_array($result['result']));
        $this->assertEquals($result['result']['tax_country_id'], $class->getTaxCountryId());
        $this->assertEquals($result['result']['tax_region_id'], $class->getTaxRegionId());
        $this->assertEquals($result['result']['tax_postcode'], $class->getTaxPostcode());
        $this->assertEquals($result['result']['code'], $class->getCode());
        $this->assertEquals($result['result']['rate'], $class->getRate());

        $expectedZipIsRange=$result['result']['zip_is_range'] == 1  ? 1 : 0;
        $this->assertEquals($expectedZipIsRange, $class->getZipIsRange() ? 1 : 0);
        if ($expectedZipIsRange) {
            $this->assertEquals($result['result']['zip_from'], $class->getZipFrom());
            $this->assertEquals($result['result']['zip_to'], $class->getZipTo());
        }
    }

    /**
     * @magentoDbIsolation enabled
     *
     */
    public function testAjaxNonLoadAction()
    {
        $postData = [ 'id' => 99999999 ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/tax/rate/ajaxLoad');
        $jsonBody = $this->getResponse()->getBody();

        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        )->jsonDecode(
            $jsonBody
        );

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success'] == false);
        $this->assertTrue(!array_key_exists('result', $result));
        $this->assertArrayHasKey('error_message', $result);
        $this->assertTrue(strlen($result['error_message'])>0);
    }
}
