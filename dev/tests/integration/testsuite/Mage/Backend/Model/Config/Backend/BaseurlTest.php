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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Config_Backend_BaseurlTest extends Mage_Backend_Area_TestCase
{
    /**
     * @param string $path
     * @param string $value
     * @magentoDbIsolation enabled
     * @dataProvider validationDataProvider
     */
    public function testValidation($path, $value)
    {
        /** @var $model Mage_Backend_Model_Config_Backend_Baseurl */
        $model = Mage::getModel('Mage_Backend_Model_Config_Backend_Baseurl');
        $model->setPath($path)->setValue($value)->save();
        $this->assertNotEmpty((int)$model->getId());
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        $basePlaceholder = '{{base_url}}';
        $unsecurePlaceholder = '{{unsecure_base_url}}';
        $unsecureSuffix = '{{unsecure_base_url}}test/';
        $securePlaceholder = '{{secure_base_url}}';
        $secureSuffix = '{{secure_base_url}}test/';

        return array(
            // any fully qualified URLs regardless of path
            array('any/path', 'http://example.com/'),
            array('any/path', 'http://example.com/uri/'),

            // unsecure base URLs
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, $basePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LIB_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LIB_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LIB_URL, $unsecureSuffix),

            // secure base URLs
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $basePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $securePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $secureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, $securePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, $secureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, $securePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, $secureSuffix),

            // secure base URLs - in addition can use unsecure
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $unsecureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, $unsecureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, $unsecurePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, $unsecureSuffix),
        );
    }

    /**
     * @param string $path
     * @param string $value
     * @magentoDbIsolation enabled
     * @expectedException Mage_Core_Exception
     * @dataProvider validationExceptionDataProvider
     */
    public function testValidationException($path, $value)
    {
        /** @var $model Mage_Backend_Model_Config_Backend_Baseurl */
        $model = Mage::getModel('Mage_Backend_Model_Config_Backend_Baseurl');
        $model->setPath($path)->setValue($value)->save();
    }

    /**
     * @return array
     */
    public function validationExceptionDataProvider()
    {
        $baseSuffix = '{{base_url}}test/';
        $unsecurePlaceholder = '{{unsecure_base_url}}';
        $unsecureSuffix = '{{unsecure_base_url}}test/';
        $unsecureWrongSuffix = '{{unsecure_base_url}}test';
        $securePlaceholder = '{{secure_base_url}}';
        $secureSuffix = '{{secure_base_url}}test/';
        $secureWrongSuffix = '{{secure_base_url}}test';

        return array(
            // not a fully qualified URLs regardless path
            array('', 'not a valid URL'),
            array('', 'example.com'),
            array('', 'http://example.com'),
            array('', 'http://example.com/uri'),

            // unsecure base URLs
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, ''), // breaks cache
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, $baseSuffix), // creates redirect loops
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, $unsecureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, $unsecurePlaceholder), // creates endless recursion
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL, $baseSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecureWrongSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecureWrongSuffix),
            array(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LIB_URL, $unsecureWrongSuffix),

            // secure base URLs
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $baseSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $secureSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, $securePlaceholder),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, ''),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $baseSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL, $secureWrongSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_MEDIA_URL, $secureWrongSuffix),
            array(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LIB_URL, $secureWrongSuffix),
        );
    }
}
