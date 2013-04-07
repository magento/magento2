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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Tax_RateControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @dataProvider ajaxSaveActionDataProvider
     */
    public function testAjaxSaveAction($postData, $expectedData)
    {
        $this->getRequest()->setPost($postData);

        $this->dispatch('backend/admin/tax_rate/ajaxSave');

        $jsonBody = $this->getResponse()->getBody();
        $result = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($jsonBody);

        $this->assertArrayHasKey('tax_calculation_rate_id', $result);

        $rateId = $result['tax_calculation_rate_id'];
        /** @var $rate Mage_Tax_Model_Calculation_Rate */
        $rate = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->load($rateId, 'tax_calculation_rate_id');
        $this->assertEquals($expectedData['zip_is_range'], $rate->getZipIsRange());
        $this->assertEquals($expectedData['zip_from'], $rate->getZipFrom());
        $this->assertEquals($expectedData['zip_to'], $rate->getZipTo());
        $this->assertEquals($expectedData['tax_postcode'], $rate->getTaxPostcode());
    }

    public function ajaxSaveActionDataProvider()
    {
        $postData = array(
            'rate' => '10',
            'tax_country_id' => 'US',
            'tax_region_id' => '0',
        );
        return array(
            array(
                $postData + array(
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '1',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ),
                array(
                    'zip_is_range' => 1,
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '10000-20000',
                )
            ),
            array(
                $postData + array(
                    'code' => 'Rate ' . uniqid(),
                    'zip_is_range' => '0',
                    'zip_from' => '10000',
                    'zip_to' => '20000',
                    'tax_postcode' => '*',
                ),
                array(
                    'zip_is_range' => null,
                    'zip_from' => null,
                    'zip_to' => null,
                    'tax_postcode' => '*',
                )
            ),
        );
    }
}
