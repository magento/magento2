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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * This test was moved to the separate file.
 * Because of fixture applying order magentoAppIsolation -> magentoDataFixture -> magentoConfigFixture
 * (https://wiki.magento.com/display/PAAS/Integration+Tests+Development+Guide
 * #IntegrationTestsDevelopmentGuide-ApplyingAnnotations)
 * config fixtures can't be applied before data fixture.
 */
class Mage_Catalog_Model_Category_CategoryImageTest extends PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $_oldLogActive;

    /** @var string */
    protected $_oldExceptionFile;

    /** @var string */
    protected $_oldWriterModel;

    protected function setUp()
    {
        $this->_oldLogActive = Mage::app()->getStore()->getConfig('dev/log/active');
        $this->_oldExceptionFile = Mage::app()->getStore()->getConfig('dev/log/exception_file');
        $this->_oldWriterModel = (string) Mage::getConfig()->getNode('global/log/core/writer_model');
    }

    protected function tearDown()
    {
        Mage::app()->getStore()->setConfig('dev/log/active', $this->_oldLogActive);
        $this->_oldLogActive = null;

        Mage::app()->getStore()->setConfig('dev/log/exception_file', $this->_oldExceptionFile);
        $this->_oldExceptionFile = null;

        Mage::getConfig()->setNode('global/log/core/writer_model', $this->_oldWriterModel);
        $this->_oldWriterModel = null;

        /**
         * @TODO: refactor this test
         * Changing store configuration in such a way totally breaks the idea of application isolation.
         * Class declaration in data fixture file is dumb too.
         * Added a quick fix to be able run separate tests with "phpunit --filter testMethod"
         */
        if (class_exists('Stub_Mage_Catalog_Model_CategoryTest_Zend_Log_Writer_Stream', false)) {
            Stub_Mage_Catalog_Model_CategoryTest_Zend_Log_Writer_Stream::$exceptions = array();
        }
    }

    /**
     * Test that there is no exception '$_FILES array is empty' in Varien_File_Uploader::_setUploadFileId()
     * if category image was not set
     *
     * @magentoDataFixture Mage/Catalog/Model/Category/_files/stub_zend_log_writer_stream.php
     * @magentoDataFixture Mage/Catalog/Model/Category/_files/category_without_image.php
     */
    public function testSaveCategoryWithoutImage()
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = Mage::registry('_fixture/Mage_Catalog_Model_Category');
        $this->assertNotEmpty($category->getId());

        foreach (Stub_Mage_Catalog_Model_CategoryTest_Zend_Log_Writer_Stream::$exceptions as $exception) {
            $this->assertNotContains('$_FILES array is empty', $exception['message']);
        }
    }
}
