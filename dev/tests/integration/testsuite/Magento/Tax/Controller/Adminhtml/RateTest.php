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
namespace Magento\Tax\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class RateTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @dataProvider ajaxSaveActionDataProvider
     * @magentoDbIsolation enabled
     */
    public function testAjaxSaveAction($postData, $expectedData)
    {
        $this->getRequest()->setPost($postData);

        $this->dispatch('backend/tax/rate/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Helper\Data'
        )->jsonDecode(
            $jsonBody
        );

        $this->assertArrayHasKey('tax_calculation_rate_id', $result);

        $rateId = $result['tax_calculation_rate_id'];
        /** @var $rate \Magento\Tax\Model\Calculation\Rate */
        $rate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Tax\Model\Calculation\Rate'
        )->load(
            $rateId,
            'tax_calculation_rate_id'
        );

        $this->assertEquals($expectedData['zip_is_range'], $rate->getZipIsRange());
        $this->assertEquals($expectedData['zip_from'], $rate->getZipFrom());
        $this->assertEquals($expectedData['zip_to'], $rate->getZipTo());
        $this->assertEquals($expectedData['tax_postcode'], $rate->getTaxPostcode());
    }

    public function ajaxSaveActionDataProvider()
    {
        $postData = array('rate' => '10', 'tax_country_id' => 'US', 'tax_region_id' => '1');
        return array(
            array(
                $postData + array(
                    'code' => 'Rate ' . uniqid(rand()),
                    'zip_is_range' => '1',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*'
                ),
                array('zip_is_range' => 1, 'zip_from' => '10000', 'zip_to' => '20000', 'tax_postcode' => '10000-20000')
            ),
            array(
                $postData + array(
                    'code' => 'Rate ' . uniqid(rand()),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*'
                ),
                array('zip_is_range' => null, 'zip_from' => null, 'zip_to' => null, 'tax_postcode' => '*')
            )
        );
    }

    /**
     * Test wrong data conditions
     *
     * @dataProvider ajaxSaveActionDataInvalidDataProvider
     * @magentoDbIsolation enabled
     */
    public function testAjaxSaveActionInvalidData($postData, $expectedData)
    {
        $this->getRequest()->setPost($postData);

        $this->dispatch('backend/tax/rate/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Helper\Data'
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
        $expectedData = array(
            'success' => false,
            'error_message' => 'Please fill all required fields with valid information.'
        );
        return array(
            array(
                // Zip as range but no range values provided
                array(
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '1',
                    'zip_from' => '',
                    'zip_to' => '',
                    'tax_postcode' => '*'
                ),
                $expectedData
            ),
            // Code is empty
            array(
                array(
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => '',
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*'
                ),
                $expectedData
            ),
            // Country ID empty
            array(
                array(
                    'rate' => rand(1, 10000),
                    'tax_country_id' => '',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*'
                ),
                $expectedData
            ),
            // Rate empty
            array(
                array(
                    'rate' => '',
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*'
                ),
                $expectedData
            ),
            // Tax zip code is empty
            array(
                array(
                    'rate' => rand(1, 10000),
                    'tax_country_id' => 'US',
                    'tax_region_id' => '0',
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => ''
                ),
                $expectedData
            ),
            // All params empty
            array(
                array(
                    'rate' => '',
                    'tax_country_id' => '',
                    'tax_region_id' => '1',
                    'code' => '',
                    'zip_is_range' => '0',
                    'zip_from' => '',
                    'zip_to' => '',
                    'tax_postcode' => ''
                ),
                $expectedData
            )
        );
    }
}
