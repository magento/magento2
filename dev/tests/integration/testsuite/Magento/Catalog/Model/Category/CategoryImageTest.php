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

/**
 * This test was moved to the separate file.
 * Because of fixture applying order magentoAppIsolation -> magentoDataFixture -> magentoConfigFixture
 * (https://wiki.magento.com/display/PAAS/Integration+Tests+Development+Guide
 * #IntegrationTestsDevelopmentGuide-ApplyingAnnotations)
 * config fixtures can't be applied before data fixture.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category\CategoryImageTest\StubZendLogWriterStreamTest;

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
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getConfig(
            'dev/log/active'
        );
        $this->_oldExceptionFile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
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
            StubZendLogWriterStreamTest::$exceptions = array();
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

        foreach (StubZendLogWriterStreamTest::$exceptions as $exception) {
            $this->assertNotContains('$_FILES array is empty', $exception['message']);
        }
    }
}
