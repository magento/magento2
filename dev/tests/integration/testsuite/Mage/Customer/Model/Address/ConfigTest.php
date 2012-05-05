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
 * @category    Magento
 * @package     Mage_Customer
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Customer_Model_Address_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Customer_Model_Address_Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model= new Mage_Customer_Model_Address_Config();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Customer/_files/address_formats.php
     */
    public function testGetFormats()
    {
        $expectedFormatEscape = array(
            'escaped_one' => true,
            'escaped_two' => false,
            'escaped_three' => false,
            'escaped_four' => false,
            'escaped_five' => false,
            'escaped_six' => true
        );

        $formats = array();
        foreach ($this->_model->getFormats() as $format) {
            $formats[$format->getCode()] = $format;
        }

        foreach ($expectedFormatEscape as $formatCode => $escapeHtml) {
            if (isset($formats[$formatCode])) {
                $format = $formats[$formatCode];
                $this->assertEquals($escapeHtml, $format->getEscapeHtml());
            } else {
                $this->fail("Missing '{$formatCode}' item in the fixture.");
            }

            $this->assertInstanceOf(
                Mage_Customer_Model_Address_Config::DEFAULT_ADDRESS_RENDERER,
                $format->getRenderer()
            );
        }
    }
}
