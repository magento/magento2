<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * This test was moved to the separate file.
 * Because of fixture applying order magentoAppIsolation -> magentoDataFixture -> magentoConfigFixture
 * (https://wiki.magento.com/display/PAAS/Integration+Tests+Development+Guide
 * #IntegrationTestsDevelopmentGuide-ApplyingAnnotations)
 * config fixtures can't be applied before data fixture.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category\CategoryImageTest\StubZendLogWriterStream;

class CategoryImageTest extends \PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $_oldLogActive;

    /** @var string */
    protected $_oldExceptionFile;

    /** @var string */
    protected $_oldWriterModel;

    protected function setUp()
    {
        $this->_oldLogActive = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore()->getConfig(
            'dev/log/active'
        );
        $this->_oldExceptionFile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore()->getConfig(
            'dev/log/exception_file'
        );
    }

    protected function tearDown()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            'dev/log/active',
            $this->_oldLogActive,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->_oldLogActive = null;

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            'dev/log/exception_file',
            $this->_oldExceptionFile,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->_oldExceptionFile = null;

        $this->_oldWriterModel = null;

        /**
         * @TODO: refactor this test
         * Changing store configuration in such a way totally breaks the idea of application isolation.
         * Class declaration in data fixture file is dumb too.
         * Added a quick fix to be able run separate tests with "phpunit --filter testMethod"
         */
        if (class_exists('Magento\Catalog\Model\Category\CategoryImageTest\StubZendLogWriterStreamTest', false)) {
            StubZendLogWriterStream::$exceptions = [];
        }
    }

    /**
     * Test that there is no exception '$_FILES array is empty' in \Magento\Framework\File\Uploader::_setUploadFileId()
     * if category image was not set
     *
     */
    public function testSaveCategoryWithoutImage()
    {
        $this->markTestSkipped('MAGETWO-15096');

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get('Magento\Framework\Registry')
            ->registry('_fixture/Magento\Catalog\Model\Category');
        $this->assertNotEmpty($category->getId());

        foreach (StubZendLogWriterStream::$exceptions as $exception) {
            $this->assertNotContains('$_FILES array is empty', $exception['message']);
        }
    }
}
